<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Models\GroupPurchaseRecord;
use App\Models\Item;
use App\Models\UserItem;
use App\Models\UserCashback;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\LockTimeoutException;

/**
 * Class GroupPurchaseRepository.
 *
 * @package namespace App\Repositories;
 */
class GroupPurchaseRepository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return GroupPurchaseRecord::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
    // 参与团购
    public function join($user_id, $item_id) {
        // $amount = request("amount", 1);
        // $amount = abs($amount);
        // 20230804: gold  购买把 数量那个 删除。就是默认1 不允许通过传参 设置 多个
        $amount = 1;

        $userRepository = app(UserRepository::class);

        $user = $userRepository->find($user_id);

        // 检查是否已经开团
        $is_open = GroupPurchaseRecord::where("status", 0)
            ->where("expired_at", ">=", now())
            ->where("item_id", $item_id)
            ->exists();
        if ($is_open) {
            // 如果已经开团，检查是否已经加入
            $is_join = GroupPurchaseRecord::where("status", 0)
                ->where("expired_at", ">=", now())
                ->where("item_id", $item_id)
                ->where("user_id", $user_id)
                ->exists();
            if ($is_join) {
                throw new \Exception("already join this group purchase team");
            }            
        }
        // 检查商品是否存在
        $item = Item::where("id", $item_id)->first();
        if (empty($item)) {
            throw new \Exception("item not exists");
        }

        // 检查剩余拼团数量（库存）
        if ($item->stock <= 0) {
            throw new \Exception("item stock not enough");
        }
        // 如果剩余拼团数量小于用户要求购买数量
        // 则仅退回多出部分的数量
        if ($item->stock < $amount) {
            $amount = $item->stock;
        }

        // 检查余额
        // 20230614: 使用可提现余额
        if ($user->balance < ($item->price * $amount)){
            throw new \Exception("balance not enough");
        }

        // 计算拼团的过期时间
        $expired_at = $item->gp_end_time;
        // 如果已经开团, 则沿用开团时记录的过期时间
        // 此逻辑已过期，调整为沿用商品信息中记录的过期时间
        // if ($is_open) {
        //     $gp_record = GroupPurchaseRecord::where("status", 0)
        //         ->where("expired_at", ">=", now())
        //         ->where("item_id", $item_id)
        //         ->first();
        //     $expired_at = $gp_record->expired_at;
        // }

        // 尝试获取锁
        $lock = Cache::lock('ITEM_JOINED_COUNT_LOCK:' . $item->id, 10);
        try {
            // 如果未获取到锁则尝试重新获取，每秒1次
            $lock->block(5);

            // 记录参团记录
            GroupPurchaseRecord::create([
                "user_id" => $user_id,
                "item_id" => $item_id,
                "expired_at" => $expired_at,
                "status" => 0,
                "amount" => $amount
            ]);

            // 增加可控参团人数字段
            $item = Item::where("id", $item_id)
                ->lockForUpdate()
                ->first();;
            $item->joined_count_display += 1;
            $item->stock -= $amount;
            $item->save();
        } catch (LockTimeoutException $e) {
            throw new \Exception("too many competitors, please try again later");
        } finally {
            $lock?->release();
        }

        // 检查是否成功结团
        $this->success($item_id);

        return $amount;
    }

    // 成功结团
    public function success($item_id) {
        // 检查商品是否存在
        $item = Item::where("id", $item_id)->first();
        if (empty($item)) {
            throw new \Exception("item not exists");
        }

        // 检查当前商品团购是否达到预定人数
        
        $query = GroupPurchaseRecord::where("status", 0)
            ->where("expired_at", ">=", now())
            ->where("item_id", $item_id);
        // 20230626: 停止使用真实的参与人数
        // $join_count = $query->count();
        // 20230626: 更换为可控参团人数
        $join_count = $item->joined_count_display;
        if ($join_count < $item->group_people_count) {
            // 如果人数不足，则停止执行
            return false;
        }

        // $userRepository = app(UserRepository::class);

        $records = $query->get();
        foreach ($records as $record) {
            $user_id = $record->user_id;
            $user = $record->user;

            // 计算sn
            $maxSn = UserItem::where("item_id", $item_id)
                ->where("user_id", $user_id)
                ->max("serial_number");
            $sn = intval($maxSn) + 1;

            DB::beginTransaction();
            try {
                // 查询现有拥有记录
                // $item_record = UserItem::where("user_id", $user_id)
                //     ->where("item_id", $item_id)
                //     ->first();
                // if (empty($item_record)) {
                    // 如果不存在，则添加拥有记录
                    $item_record = UserItem::create([
                        "user_id" => $user_id,
                        "item_id" => $item_id,
                        "earning_end_at" => now()->addDays($item->gain_day_num)->endOfDay(),
                        "serial_number" => $sn,
                        "last_earning_at" => now(),
                        "amount" => $record->amount
                    ]);
                // } else {
                //     // 如果存在，直接添加拥有数量
                //     $item_record->amount += $record->amount;
                //     $item_record->save();
                // }

                // 扣除余额
                // $userRepository->addBalance([
                //     "user_id" => $user_id,
                //     "money" => 0 - $item->price,
                //     "log_type" => 6,
                //     "item_id" => $item_id
                // ]);
                if ($user->lv1_superior_id) {
                    // 为上级返现
                    UserCashback::create([
                        "user_id" => $user->lv1_superior_id,
                        "pay_uid" => $user_id,
                        "item_id" => $item_id,
                        "pay_amount" => $item->price,
                        "back_amount" => $item->cashback,
                        "status" => 0,
                        "log_type" => 3
                    ]);
                }
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                throw new \Exception("buy error");
            }
        }
        $query->update(["status" => 1]);

    }
}
