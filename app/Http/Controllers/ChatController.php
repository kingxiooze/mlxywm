<?php

namespace App\Http\Controllers;

use App\Events\SendChatMessage;
use App\Models\Chat\Member;
use App\Models\Chat\Record;
use App\Models\Chat\Room;
use App\Models\User;
use App\Repositories\ChatRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ChatController extends Controller
{

    protected function getRepositoryClass(){
        return ChatRepository::class;
    }

    // 我的聊天室列表
    public function getMyRoom() {
        $rooms = Room::whereHas("members", function(Builder $query){
            $query->where("user_id", auth()->id());
        })
        ->leftJoin("chat_records", function($query){
            $query->on("chat_rooms.id", "=", "chat_records.room_id")
                ->whereRaw("chat_records.id IN (select MAX(r2.id) from chat_records as r2 join chat_rooms as m2 on m2.id = r2.room_id group by m2.id)");
        })
        ->select(
            "chat_rooms.id", "chat_rooms.name",
            "chat_rooms.avatar",
            "chat_records.user_id",
            "chat_records.content", "chat_records.record_type", 
            // "chat_records.redpacket_amount",
            // "chat_records.redpacket_count",
            "chat_records.created_at"
        )
        ->orderBy("chat_records.created_at", "desc")
        ->paginate()
        ->getCollection()->transform(function ($value) {
            if (empty($value->user_id)) {
                $record = null;
            } else {
                $user = User::select("id", "name", "avatar")
                    ->where("id", $value->user_id)
                    ->first()
                    ->toArray();
                $record = [
                    "user" => $user,
                    "content" => json_decode($value->content, true),
                    "type" => $value->record_type,
                    // "redpacket_amount" => $value->redpacket_amount,
                    // "redpacket_count" => $value->redpacket_count,
                    "created_at" => $value->created_at
                ];
            }

            return [
                "id" => $value->id,
                "name" => $value->name,
                "avatar" => $value->avatar,
                "record" => $record 
            ];
        });

        return $this->success($rooms);
    }

    // 聊天室用户列表
    public function getRoomUser(Request $request) {
        $room_id = $request->input("room_id", null);
        $username = $request->input("username", null);

        if ($room_id == null) {
            return $this->errorBadRequest("need room id");
        }
        $query = Member::where("room_id", $room_id)
            ->with("user:id,avatar,name")
            ->orderBy("created_at", "asc");

        if ($username != null) {
            $query->whereHas("user", function(Builder $query) use ($username){
                $query->where("name", "like", $username . "%");
            });
        }

        $users = $query->paginate();
        return $this->success($users);

    }

    // 禁言用户
    public function postMuteMember(Request $request) {
        $user_id = $request->input("user_id", null);
        $room_id = $request->input("room_id", null);

        $room = Room::where("id", $room_id)->first();
        if (empty($room)) {
            return $this->errorNotFound("room not exists");
        }

        if ($room->user_id != auth()->id()) {
            return $this->errorForbidden("only for room owner");
        }

        $member = Member::where("user_id", $user_id)
            ->where("room_id", $room_id)
            ->first();
        if (empty($room)) {
            return $this->errorNotFound("target user not join room");
        }
        $member->is_mute = !boolval($member->is_mute);
        $member->save();

        return $this->ok();
    }

    // 聊天记录
    public function getRoomChatRecord(Request $request) {
        $user = auth()->user();
        $room_id = $request->input("room_id", null);
        $room = Room::where("id", $room_id)->first();
        if (empty($room)) {
            return $this->errorNotFound("room not exists");
        }

        $is_joined = Member::where("user_id", $user->id)
            ->where("room_id", $room_id)
            ->exists();
        if (!$is_joined) {
            return $this->errorForbidden("room not exists");
        }

        $records = Record::where("room_id", $room_id)
            ->with("user:id,avatar,name")
            ->orderBy("created_at", "desc")
            ->paginate(20);

        return $this->success($records);
    }

    // 发送消息
    public function postSend(Request $request) {
        $data = $request->validate([
            'room_id' => "required", 
            // 'user_id' => "required",
            'content' => "required",
            'record_type' => "required",
            'redpacket_amount' => "",
            'redpacket_count' => "",
        ]);

        $user = auth()->user();

        $member = Member::where("user_id", $user->id)
            ->where("room_id", $data["room_id"])
            ->first();
        if (empty($member)) {
            return $this->errorForbidden("you are not in this chat room");
        }

        if ($member->is_mute) {
            return $this->errorForbidden("you have been muted");
        }

        $data["user_id"] = $user->id;
        $record = Record::create($data);

        SendChatMessage::broadcast($record->load("speaker"));

        return $this->ok();
    }

    // 打开聊天红包
    public function postOpenChatRedPacket(Request $request) {
        $record_id = $request->input("record_id", null);

        if (empty($record_id)) {
            return $this->errorBadRequest("need chat record id");
        }

        $lock = Cache::lock('chat_red_packet_' . $record_id, 2);

        $is_lock = false;
        do {
            $is_lock = $lock->get();
        } while (!$is_lock);

        $data = [];

        try {
            $data["amount"] = $this->repository->openRedPacket($record_id);
            $info = $this->repository->getRedPacketInfo($record_id);
            $data = array_merge($data, $info);
        } catch (\Throwable $th) {
            return $this->errorBadRequest($th->getMessage());
        } finally {
            $lock->release();
        }

        return $this->success($data);
        
    }

    // 新建聊天室
    public function postCreateRoom(Request $request) {
        $data = $request->validate([
            'name' => "required", 
            'uplimit' => "required",
            'content' => "required",
        ]);

        $user = auth()->user();

        if (!$user->is_salesman) {
            return $this->errorForbidden("Only salesmen can create new rooms");
        }
        $data["user_id"] = $user->id;
        $data["avatar"] = $user->avatar;
        $room = Room::create($data);

        // 将业务员加入房间
        Member::create([
            "room_id" => $room->id,
            "user_id" => $user->id,
            "is_mute" => false
        ]);

        return $this->success($room);
    }

    // 邀请聊天
    public function postInviteChat(Request $request) {
        $data = $request->validate([
            'mobile' => "required", 
            'room_id' => "",
        ]);

        $user = auth()->user();

        if (!$user->is_salesman) {
            return $this->errorForbidden("Only salesmen can invite user");
        }

        $target = User::where("mobile", $data["mobile"])
            ->first();
        if (empty($target)) {
            return $this->errorNotFound("target user not exists");
        }


        if (!array_key_exists("room_id", $data)) {
            $room_name = "ChatTo" . $target->id;

            $room = Room::where("name", $room_name)
                ->where("user_id", $user->id)
                ->whereHas("members", function (Builder $query) use ($target){
                    $query->where("user_id", $target->id);
                })
                ->first();

            if (!empty($room)) {
                return $this->success(["room_id" => $room->id]);
            }

            $room = Room::create([
                'name' => $room_name, 
                'uplimit' => 2,
                'content' => "",
                "user_id" => $user->id,
                "avatar" => $user->avatar
            ]);

            // 业务员
            Member::create([
                "room_id" => $room->id,
                "user_id" => $user->id,
                "is_mute" => false
            ]);
            // 目标用户
            Member::create([
                "room_id" => $room->id,
                "user_id" => $target->id,
                "is_mute" => false
            ]);

            return $this->success(["room_id" => $room->id]);
        } else {
            $relation = Member::where("user_id", $target->id)
                ->where("room_id", $data["room_id"])
                ->first();

            if (empty($relation)) {
                $room = Room::where("id", $data["room_id"])->first();
                if (empty($room)) {
                    return $this->errorNotFound("room not exists");
                }

                $joined_count = Member::where("room_id", $data["room_id"])->count();
                if ($joined_count >= $room->uplimit) {
                    return $this->errorBadRequest("room is fulled");
                }
                Member::create([
                    "room_id" => $data["room_id"],
                    "user_id" => $target->id,
                    "is_mute" => false
                ]);
            }

            return $this->success(["room_id" => $data["room_id"]]);
        }

        return $this->ok();
    }

    // 删除用户
    public function postRemoveUser(Request $request) {
        $data = $request->validate([
            'room_id' => "required",
            'user_id' => "required", 
        ]);

        $user = auth()->user();

        if (!$user->is_salesman) {
            return $this->errorForbidden("only salesmen can invite user");
        }

        $target = User::where("id", $data["user_id"])
            ->first();

        if (empty($target)) {
            return $this->errorNotFound("target user not exists");
        }

        Member::where("room_id", $data["room_id"])
            ->where("user_id", $data["user_id"])
            ->delete();

        return $this->ok();
    }

    // 修改房间信息
    public function postChangeRoomInfo(Request $request) {
        $data = $request->validate([
            'room_id' => "required",
            'avatar' => "",
            'content' => "",
        ]);

        $user = auth()->user();

        if (!$user->is_salesman) {
            return $this->errorForbidden("only salesmen can change room info");
        }

        $room = Room::where("id", $data["room_id"])->first();
        if (empty($room)) {
            return $this->errorNotFound("room not exists");
        }

        if ($room->user_id != $user->id) {
            return $this->errorForbidden("only owner can change room info");
        }

        if (array_key_exists("avatar", $data)) {
            $room->avatar = $data["avatar"];
        }

        if (array_key_exists("content", $data)) {
            $room->content = $data["content"];
        }

        $room->save();

        return $this->ok();
    }

    // 批量移除聊天记录
    public function postBatchRemoveRecord(Request $request) {
        $data = $request->validate([
            'room_id' => "required",
            'records' => "required|array"
        ]);

        $user = auth()->user();

        if (!$user->is_salesman) {
            return $this->errorForbidden("only salesmen can remove chat records");
        }

        $room = Room::where("id", $data["room_id"])->first();
        if (empty($room)) {
            return $this->errorNotFound("room not exists");
        }

        if ($room->user_id != $user->id) {
            return $this->errorForbidden("only owner can remove chat records");
        }

        Record::whereIn("id", $data["records"])
            ->whereHas("room", function(Builder $query) use ($user) {
                $query->where("user_id", $user->id);
            })->delete();

        return $this->ok();
    }

    // 聊天室详情
    public function getRoomInfo(Request $request) {
        $room_id = $request->input("room_id", null);

        if (empty($room_id)) {
            return $this->errorNotFound("room not exists");
        }

        $room = Room::where("id", $room_id)->first();
        if (empty($room)) {
            return $this->errorNotFound("room not exists");
        }

        return $this->success($room);
    }

}
