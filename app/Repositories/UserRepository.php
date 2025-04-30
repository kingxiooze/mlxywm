<?php

namespace App\Repositories;

use App\Models\Item;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Models\User;
use App\Models\UserCashback;
use App\Models\MoneyLog;
use App\Models\UserItem;
use App\Models\Chat\Room;
use App\Models\Chat\Member;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Class UserRepository.
 *
 * @package namespace App\Repositories;
 */
class UserRepository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return User::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    // 查询往上三级的用户ID
    public function queryDiffLevelSuperior($superiorCode){
        $result = [
            "lv1_superior_id" => null,
            "lv2_superior_id" => null,
            "lv3_superior_id" => null
        ];

        // 查上级
        $lv1_superior = User::where("code", $superiorCode)->first();
        if (empty($lv1_superior)) {
            return $result;
        }
        $result["lv1_superior_id"] = $lv1_superior->id;

        // 查上上级
        $lv2_superior = $lv1_superior->parent;
        if (empty($lv2_superior)) {
            return $result;
        }
        $result["lv2_superior_id"] = $lv2_superior->id;

        // 查上上上级
        $lv3_superior = $lv2_superior->parent;
        if (empty($lv3_superior)) {
            return $result;
        }
        $result["lv3_superior_id"] = $lv3_superior->id;

        return $result;
        
    }

    public function incomeState($user_id) {
        $total = UserCashback::where("user_id", $user_id)
            // ->where("status", 0)
            ->sum("back_amount");
        $unsettled = UserCashback::where("user_id", $user_id)
            ->where("status", 0)
            ->sum("back_amount");
    }

    // 添加余额
    // 参数为MoneyLog里面的字段
    public function addBalance($data) {
        DB::beginTransaction();
        try {
            $user = User::where("id", $data["user_id"])
                ->lockForUpdate()
                ->first();
            if ($user) {
                // 确定类型
                $balance_type = Arr::get($data, "balance_type", "balance");
                if($balance_type == "balance") {
                    $balance_type_value = "1";
                } else if($balance_type == "redpacket_balance") {
                    $balance_type_value = "2";
                } else if($balance_type == "mission_balance") {
                    $balance_type_value = "3";
                }
                // 20230918: 所有记录资金流水 如果金额为0 的都不添加记录。 
                if ($data["money"] == 0) {
                    return;
                }
                // 检查余额
                if (($user->$balance_type + $data["money"]) < 0) {
                    throw new \Exception("balance not enough");
                }
                // 资金记录表添加记录
                MoneyLog::create([
                    "user_id" => $data["user_id"],
                    "money" => $data["money"],
                    "balance_type" => $balance_type_value,
                    "log_type" => $data["log_type"],
                    "before_change" => $user->$balance_type,
                    "item_id" => $data["item_id"] ?? null,
                    "source_uid" => $data["source_uid"] ?? null,
                    "user_item_id" => $data["user_item_id"] ?? null
                ]);
                // 为用户增加余额
                $user->$balance_type += floatval($data["money"]);
                $user->save();
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            throw new \Exception($th->getMessage());
        }
    }

    // 用户注册
    public function register($data) {
        if (array_key_exists("parent_code", $data)) {
            $superior_codes = $this
                ->queryDiffLevelSuperior(
                    $data["parent_code"]
                );
            $data = array_merge($data, $superior_codes);
        }
        

        $user = null;
        DB::beginTransaction();
        try {
            $user = User::create($data);
            if ($user->parent) {
                
                // 为上级增加邀请人数
                $user->parent->total_invite += 1;
                // 当需要用户自动领取邀请奖励时，添加已领取数量
                // $user->parent->received_invite += 1;
                // 当需要用户主动领取邀请奖励时，添加未领取数量
                $user->parent->unreceive_invite += 1;
                $user->parent->save();

                // 20230404 恢复成用户主动去领
                // $invite_reward = (int) setting('INVITE_USER_REWARD');
                // $invite_limit = (int) setting('REWARD_INVITE_USER_COUNT');
                // if ($user->parent->total_invite <= $invite_limit) {
                //     // 发放邀请奖励
                //     // 注册时立即发放，不用手动领取
                //     $this->repository->addBalance([
                //         "user_id" => $user->parent->id,
                //         "money" => $invite_reward,
                //         "log_type" => 7,
                //         "source_uid" => $user->id
                //     ]);
                // }

                // 创建用户时同步业务员码
                $user->salesman_code = $user->parent->salesman_code;
                $user->save();

                // 如果上级是业务员，则创建一个私聊房间
                if ($user->parent->is_salesman) {
                    $room_name = "ChatTo" . $user->id;
                    $room = Room::create([
                        'name' => $room_name, 
                        'uplimit' => 2,
                        'content' => "",
                        "user_id" => $user->parent->id,
                        "avatar" => $user->parent->avatar
                    ]);
        
                    // 业务员
                    Member::create([
                        "room_id" => $room->id,
                        "user_id" => $user->parent->id,
                        "is_mute" => false
                    ]);
                    // 目标用户
                    Member::create([
                        "room_id" => $room->id,
                        "user_id" => $user->id,
                        "is_mute" => false
                    ]);

                    // 20230714: 如果上级是业务员，则加入业务员的公共房间
                    $salesman_room = Room::where("user_id", $user->parent->id)
                        ->where("key", $user->parent->salesman_code)
                        ->first();
                    if (empty($salesman_room)) {
                        // 不存在则新建
                        $salesman_room = Room::create([
                            'name' => $user->parent->name . '\'s room', 
                            'uplimit' => 50000,
                            'content' => "",
                            "user_id" => $user->parent->id,
                            "avatar" => $user->parent->avatar,
                            "key" => $user->parent->salesman_code
                        ]);
                    }

                    Member::create([
                        "room_id" => $salesman_room->id,
                        "user_id" => $user->id,
                        "is_mute" => false
                    ]);
                }
            }
            // 发送用户自己的注册奖励
            // 20230425: 用户注册后获得100取消
            // $reg_reward = setting("REG_REWARD_AMOUNT", 100);
            // $this->repository->addBalance([
            //     "user_id" => $user->id,
            //     "money" => $reg_reward,
            //     "log_type" => 9
            // ]);
            // 20230705: 用户注册成功后获得指定商品指定数量
            $reg_reward = explode(",", setting("REG_REWARD_ITEMS", "1,1"));
            if ($reg_reward) {
                $item = Item::where("id", $reg_reward[0])->lockForUpdate()->first();
                if (!empty($item)) {
                    $cost_result = $item->costStock($reg_reward[1]);
                    if ($cost_result) {
                        UserItem::create([
                            "user_id" => $user->id,
                            "item_id" => $item->id,
                            "earning_end_at" => now()->addDays($item->gain_day_num),
                            "serial_number" => 1,
                            "last_earning_at" => now(),
                            "amount" => $reg_reward[1]
                        ]);
                    }
                }
            }

            // 20230714: 加入公共房间
            $public_room = Room::where("key", "public")
                ->orderBy("created_at", "desc")
                ->first();
            if ($public_room) {
                Member::create([
                    "room_id" => $public_room->id,
                    "user_id" => $user->id,
                    "is_mute" => false
                ]);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw new \Exception("register error");
            // return $this->errorBadRequest("register error");
        }

        if (empty($user)) {
            throw new \Exception("register fail");
        } 

        return $user;

    }

    // 刷新最后登录时间
    public function refreshLastLoginTime($user_id) {
        DB::beginTransaction();
        try {
            $user = User::where("id", $user_id)
                ->lockForUpdate()
                ->first();
            if ($user) {
                $user->last_login_time = now();
                $user->save();
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            throw new \Exception($th->getMessage());
        }
    }
    
}
