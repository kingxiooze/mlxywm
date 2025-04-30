<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Models\Chat\Record;
use App\Models\Chat\RedPacket;
use Illuminate\Support\Facades\Log;

/**
 * Class ChatRepository.
 *
 * @package namespace App\Repositories;
 */
class ChatRepository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Record::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    // 打开聊天红包
    public function openRedPacket($record_id) {
        $user = auth()->user();
        $record = $this->find($record_id);

        if (empty($record)) {
            throw new \Exception("chat record is not exists");
        }

        if ($record->record_type != 2) {
            throw new \Exception("chat record is not red packet");
        }

        $taked_record = RedPacket::where("user_id", $user->id)
            ->where("record_id", $record->id)
            ->orderBy("created_at", "desc")
            ->first();
        if ($taked_record) {
            return $taked_record->amount;
        }

        $state = RedPacket::where("record_id", $record_id)
            ->selectRaw("sum(amount) as s, count(id) as c")
            ->first();

        $taked_count = (float) $state->c;
        $taked_amount = (float) $state->s;

        if ($taked_count >= $record->redpacket_count) {
            return 0;
        }

        // 未分配的余额
        $total = $record->redpacket_amount - $taked_amount;
        
        $remind_count = $record->redpacket_count - $taked_count;
        if ($remind_count > 1) {
            // 红包总数
            $num = $record->redpacket_count;
            // 每个红包最小金额
            $min = 0.1;
            // 已经领走的数量
            $i = $taked_count;

            $safe_total = ($total - ($num - $i) * $min) / ($num - $i) * 2; //随机安全上限
            $money = intval(mt_rand($min * 10, $safe_total * 10)) / 10;
        } else {
            $money = $total;
        }
        

        RedPacket::create([
            "user_id" => $user->id,
            "record_id" => $record->id,
            "amount" => $money
        ]);

        // 增加余额
        $userRepository = app(UserRepository::class);
        $userRepository->addBalance([
            "user_id" => $user->id,
            "money" => $money,
            "log_type" => 15
        ]);

        return $money;
    }

    // 获取红包信息
    public function getRedPacketInfo($record_id) {
        $data = [];

        $user = auth()->user();
        $record = $this->find($record_id);

        if (empty($record)) {
            throw new \Exception("chat record is not exists");
        }

        if ($record->record_type != 2) {
            throw new \Exception("chat record is not red packet");
        }

        $is_taked = RedPacket::where("user_id", $user->id)
            ->where("record_id", $record->id)
            ->exists();
        $data["is_taked"] = $is_taked;

        $state = RedPacket::where("record_id", $record_id)
            ->selectRaw("sum(amount) as s, count(id) as c")
            ->first();

        $taked_count = (float) $state->c;
        $taked_amount = (string) $state->s;

        $data["taked_count"] = $taked_count;
        $data["taked_amount"] = $taked_amount;

        $data["total_count"] = $record->redpacket_count;
        $data["total_amount"] = $record->redpacket_amount;

        $user_list = RedPacket::where("record_id", $record->id)
            ->with("user:id,avatar,name")
            ->orderBy("amount", "desc")
            ->get();
        
        $data["users"] = $user_list;

        $data["content"] = $record->content;
        $data["speaker"] = $record->speaker;

        return $data;
    }
    
}
