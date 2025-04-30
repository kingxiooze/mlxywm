<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Models\RedpacketsV2;
use App\Models\RedpacketsV2Log;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Class RedpacketsV2Repository.
 *
 * @package namespace App\Repositories;
 */
class RedpacketsV2Repository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return RedpacketsV2::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
    // 打开红包
    public function open($id) {
        $user = auth()->user();

        DB::beginTransaction();
        try {

            $redpacket = $this->find($id);

            if (empty($redpacket)) {
                throw new \Exception("gift is not exists");
            }

            if ($redpacket->is_sale == 0) {
                // 未上架
                throw new \Exception("gift box is not on sale");
            }

            if ($redpacket->status == 0) {
                // 已开完
                throw new \Exception("There are no gift boxes");
            }
        
             if ($user->is_open_v2 == 0) {
                // 联系客服 才有开盒资格
                throw new \Exception("Please contact customer service to receive the gift box qualification");
            }
            // 非有效用户
            // if ($user->is_recharged_or_buyed == 0) {
            //     throw new \Exception("You must recharge first");
            // }
             // 非有效用户
            if ($user->is_recharged_or_buyed == 0) {
                if($redpacket->is_vip==1){
                   throw new \Exception("Only the current top up user can receive the gift box."); 
                }
            }else{
                //  if($redpacket->is_vip==0){
                //  throw new \Exception("The gift box is only available to ordinary users.");
                // }
            }
           
            
            $taked_record = RedpacketsV2Log::where("user_id", $user->id)
                ->where("redpacket_id", $redpacket->id)
                // ->whereDate("created_at", today())
                // ->orderBy("created_at", "desc")
                ->first();
            if ($taked_record) {
                // return $taked_record->amount;
                // 20230922: 已领取时返回报错
                throw new \Exception("Each gift box can only be opened once.");
            }
            
            
             $taked_record2 = RedpacketsV2Log::where("user_id", $user->id)
                //->where("redpacket_id", $redpacket->id)
                ->whereDate("created_at", today())
                // ->orderBy("created_at", "desc")
                ->first();
            if ($taked_record2) {
                // return $taked_record->amount;
                // 20230922: 已领取时返回报错
                throw new \Exception("You can only open one gift box per day.");
            }
            // 统计已经领取的数量和金额
            $state = RedpacketsV2Log::where("redpacket_id", $redpacket->id)
                ->selectRaw("sum(amount) as s, count(id) as c")
                ->first();

            // 已领取的数量
            $taked_count = (float) $state->c;
            // 已发放的金额
            $taked_amount = (float) $state->s;

            // 未分配的余额
            $total = $redpacket->amount - $taked_amount;
            
            // 未领的数量
            $remind_count = $redpacket->count - $taked_count;
            if ($remind_count > 1) {
                // 红包总数
                $num = $redpacket->count;
                // 每个红包最小金额
                $min = 0.1;
                // 已经领走的数量
                $i = $taked_count;

                $safe_total = ($total - ($num - $i) * $min) / ($num - $i) * 2; //随机安全上限
                $money = intval(mt_rand($min * 10, $safe_total * 10)) / 10;
            } else {
                $money = $total;
            }
            
            RedpacketsV2Log::create([
                "user_id" => $user->id,
                "redpacket_id" => $redpacket->id,
                "amount" => $money
            ]);

            // 增加余额
            $userRepository = app(UserRepository::class);
            $userRepository->addBalance([
                "user_id" => $user->id,
                "money" => $money,
                "log_type" => 22
            ]);

            // 最后一个
            if ($remind_count == 1) {
                $redpacket->status = 0;
                $redpacket->save();
            }

            DB::commit();
            
            return strval($money);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw new \Exception($th->getMessage());
        }
        
    }

    // 获取红包信息
    public function getInfo($id) {
        $data = [];

        $user = auth()->user();
        $redpacket = $this->find($id);

        if (empty($redpacket)) {
            throw new \Exception("red packet is not exists");
        }

        if ($redpacket->is_sale == 0) {
            // 未上架
            throw new \Exception("red packet is not on sale");
        }

        // 是否已经领取
        $is_taked = RedpacketsV2Log::where("user_id", $user->id)
            ->where("redpacket_id", $redpacket->id)
            ->whereDate("created_at", today())
            ->exists();
        $data["is_taked"] = $is_taked;

        // 统计
        $state = RedpacketsV2Log::where("redpacket_id", $redpacket->id)
            ->selectRaw("sum(amount) as s, count(id) as c")
            ->first();

        $taked_count = (float) $state->c;
        $taked_amount = (string) $state->s;

        $data["taked_count"] = $taked_count;
        $data["taked_amount"] = $taked_amount;

        $data["total_count"] = $redpacket->count;
        $data["total_amount"] = $redpacket->amount;

        // 已经领取的红包记录列表
        $data["logs"] = $this->getLogList($redpacket->id);

        $data["remark"] = $redpacket->remark;

        return $data;
    }

    // 已经领取的红包记录列表
    public function getLogList($id) {
        $log_list = RedpacketsV2Log::where("redpacket_id", $id)
            ->with("user:id,avatar,mobile")
            ->orderBy("amount", "desc")
            ->get();
             //->paginate(10);
        foreach ($log_list as $log) {
           $log->user->mobile = Str::mask($log->user->mobile, "*", 3, 5);
        }

        return $log_list;
    }
}
