<?php

namespace App\Repositories;

use App\Jobs\ItemEarningManual;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Models\Item;
use App\Models\GroupPurchaseRecord;
use App\Models\MoneyLog;
use App\Models\User;
use App\Models\UserCashback;
use App\Models\UserCoupon;
use App\Models\UserItem;
use App\Models\Whitelist;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\LockTimeoutException;

/**
 * Class ItemRepository.
 *
 * @package namespace App\Repositories;
 */
class ItemRepository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Item::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    // 购买商品
    public function purchase($item_id, $user_id,$upitem_id) {
        $item = $this->find($item_id);

        // 检查是否上架
        if (!$item->is_sell) {
            throw new \Exception("item not on sell");
            
        }

        if ($item->is_group_purchase == 1) {
            return $this->groupPurchaseItem($item_id, $user_id);
        } else {
            return $this->buyItem($item_id, $user_id,$upitem_id);
        }
    }

    // 余额直接购买商品
    public function buyItem($item_id, $user_id,$upitem_id) {
        $item = $this->find($item_id);
        // $amount = request("amount", 1);
        // $amount = abs($amount);
        // 20230804: gold  购买把 数量那个 删除。就是默认1 不允许通过传参 设置 多个
        $amount = 1;
        $user_coupon_id = request("user_coupon_id", 0);

        $userRepository = app(UserRepository::class);

        $user = $userRepository->find($user_id);

        // 获取购买限制刷新时间点
        $time_limit = setting("STAT_TIME_LIMIT", "07:47");

        // 检查购买数量
        $ownerCount = UserItem::where("item_id", $item_id)
            ->where("user_id", $user_id)
            // ->where("earning_end_at", ">=", now())
            // ->where("created_at", ">=", now()->previous($time_limit))
            // ->where("created_at", "<", now()->next($time_limit))
            ->sum("amount");
        if ($ownerCount >= $item->purchase_limit) {
            throw new \Exception("exceeding the purchase quantity limit");
        }

        // 检查库存
        if ($item->stock <= 0) {
            throw new \Exception("item stock not enough");
        }
        // 如果剩余库存数量小于用户要求购买数量
        // 则仅购买剩余库存数量的商品
        if ($item->stock < $amount) {
            $amount = $item->stock;
        }

        // 检查余额
        // 20230614: 使用可提现余额
         //如果升级藏品id 不为空 
        if($upitem_id!=null){
           //查询升级藏品信息
           $userItem = UserItem::find($upitem_id);
           if(empty($userItem)){
               throw new \Exception("You don't have this item");
           }
     
           if($userItem->item->up_item != $item->id){
                throw new \Exception("Unable to upgrade to this project");
           }
            if ($user->balance < ($item->price - $userItem->item->price)){
            throw new \Exception(" balance is not enough");
             }
           
        }else{
            if ($user->balance < ($item->price * $amount)){
            throw new \Exception(" balance is not enough");
                }
        }
        
        //
       

        // 计算sn
        $maxSn = UserItem::where("item_id", $item_id)
            ->where("user_id", $user_id)
            ->max("serial_number");
        $sn = intval($maxSn) + 1;

        // 检查优惠券
        if ($user_coupon_id) {
            $user_coupon = UserCoupon::where("id", $user_coupon_id)->first();
            if (empty($user_coupon)) {
                throw new \Exception("user coupon id not exists");
            }

            if (now() > $user_coupon->expire_at) {
                throw new \Exception("coupon is expired");
            }

            if ($user_coupon->status == 1) {
                throw new \Exception("coupon is used");
            }
        }

        // 20230914: amd的商品购买受发售时间控制功能迁移过来
        // 检查预售时间
        if ($item->presale_start_at && $item->presale_end_at) {
            if (now() < $item->presale_start_at) {
                throw new \Exception("presale not start");
            } 

            if (now() > $item->presale_end_at) {
                throw new \Exception("presale is end");
            } 
        }

        // 20230921: 商品增加好友充值购开关
        // （就是用户想买这个商品必须下级有充值用户才可以购买，
        // 默认关，开启后才判断，开启未满足条件提醒
        // “Event products, this product requires you to invite 
        // friends to recharge you can buy”）        
        $frb_count = setting("ITEM_FRIEND_RECHARGE_BUY", 0);
        if ($frb_count > 0) {
            $recharged_friend_count = User::where("lv1_superior_id", $user_id)
                ->where("is_recharged_or_buyed", 1)
                ->count();
            if ($recharged_friend_count < $frb_count) {
                throw new \Exception("Event products, this product requires you to invite friends to recharge you can buy");
            }
        }

        // 20230926: 商品增加白名单开关
        // 如果开了白名单就必须是白名单的用户才能购买，
        // 且购买数量受白名单数量字段限制
        if ($item->whitelist_status) {
            $whitelist = Whitelist::where("user_id", $user->id)
                ->where("item_id", $item->id)
                ->first();
            if (empty($whitelist) || $whitelist->amount <= 0) {
                throw new \Exception("Whitelist products, this product requires you on the whitelist");
            }
        }
        
        
        if($item->location>0){
           //查询总充值金额
            $sumRecharge = MoneyLog::where("user_id",$user_id)->where("log_type",1)->sum("money"); 
            if($sumRecharge){
               if($item->location>$sumRecharge){
                  throw new \Exception("You need to recharge a total of ".$item->location." to purchase");  
                } 
            }
            
        }    
       // $hash = $this->get_hash();  
        
        
        DB::beginTransaction();
        try {
            
             //一个用户只反一次
             $record = UserItem::where("user_id", $user_id)
                ->where("item_id", $item_id)
                ->first();
            if (empty($record)) {
                 // 为上级返现
                 
                 $this->cashbackOnBuyItem($item_id, $user_id);
            }
            
            //每次都反
            
            //  if($item->is_cash_xz==0){
            //      $this->cashbackOnBuyItem($item_id, $user_id);
            //  }else{
            //      //判断上级是否购买过当前商品id
            //     //  $is_lv1_buy=UserItem::where("user_id",$user->lv1_superior_id)
            //     //  ->where("item_id",$item->id)
            //     //  ->exists();
            //     $is_lv1_buy = User::find($user->lv1_superior_id);
            //      if($is_lv1_buy->asset_value >= $item->price){
            //          $this->cashbackOnBuyItem($item_id, $user_id);
            //      }else{
            //          //没有就不进
            //      }
            //  } 
             
            // 查询现有拥有记录
            // $record = UserItem::where("user_id", $user_id)
            //     ->where("item_id", $item_id)
            //     ->first();
            //  if (empty($record)) {
                // 如果不存在，则添加拥有记录
                $record = UserItem::create([
                    "user_id" => $user_id,
                    "item_id" => $item_id,
                    "earning_end_at" => now()->addDays($item->gain_day_num)->endOfDay(),
                    "serial_number" => $sn,
                    "last_earning_at" => now(),
                    "amount" => $amount
                ]);
            // } else {
            //     // 如果存在，直接添加拥有数量
            //     $record->amount += $amount;
            //     $record->save();
            //  }
             if($upitem_id!=null){
                $price = $item->price - $userItem ->item->price;  
                //删除升级产品
                  $userItem->deleted_at = now();
                  $userItem->save();
             }else{
                $price = $item->price * $amount;   
             }
           
            if ($user_coupon_id) {
                $user_coupon = UserCoupon::where("id", $user_coupon_id)
                    ->lockForUpdate()
                    ->first();
                $price = $price * floatval($user_coupon->coupon->discount);
                // 将优惠券标记为已使用
                $user_coupon->status = 1;
                $user_coupon->item_id = $item_id;
                $user_coupon->save();
            }
            
            // 扣除余额
            $userRepository->addBalance([
                "user_id" => $user_id,
                "money" => 0 - $price,
                "log_type" => 6,
                "item_id" => $item_id,
                // 20230614: 使用可提现余额
                "balance_type" => "balance",
            ]);
             
            $item = Item::where("id", $item_id)
                ->lockForUpdate()
                ->first();
            // 商品扣除库存
            $item->stock -= $amount;
            $item->save();

            // 20230926: 商品增加白名单开关
            // 如果开了白名单就必须是白名单的用户才能购买，
            // 且购买数量受白名单数量字段限制
            if ($item->whitelist_status) {
                $whitelist = Whitelist::where("user_id", $user->id)
                    ->where("item_id", $item->id)
                    ->first();
                $whitelist->amount -= 1;
                $whitelist->save();
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw new \Exception("buy error");
        }

        return $amount;
    }
  public  function get_hash(){ 
  $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()+-'; 
  $random = $chars[mt_rand(0,73)].$chars[mt_rand(0,73)].$chars[mt_rand(0,73)].$chars[mt_rand(0,73)].$chars[mt_rand(0,73)];//Random 5 times 
  $content = uniqid().$random;  // 类似 5443e09c27bf4aB4uT 
  return sha1($content);  
} 
 // 购买商品后返现(三级)
    public function cashbackOnBuyItem($item_id, $user_id) {
        $item = $this->find($item_id);
        $userRepository = app(UserRepository::class);
        $user = $userRepository->find($user_id);

        // 上级用户返现
        if ($user->lv1_superior_id) {
            //查询上级今天获得了几次
            // $getcashback = UserCashback::where("user_id",$user->lv1_superior_id)
            // ->whereDay("created_at",today())
            // ->where("item_id",$item->id)
            // ->count();
           
                 // 为上级返现
            $cashback = UserCashback::create([
                "user_id" => $user->lv1_superior_id,
                "pay_uid" => $user_id,
                "item_id" => $item_id,
                "pay_amount" => $item->price,
                "back_amount" => $item->cashback,
                "status" => 0,
                "log_type" => 3
            ]);
             
           
            $cashback->receive();
        }
        // 上上级用户返现
        if ($user->lv2_superior_id) {
            $rate_pec = setting("ITEM_LV2_SUPERIOR_CASHBACK_RATE", 5);
            $rate = floatval($rate_pec) / 100;
            // 为上上级返现
            $cashback = UserCashback::create([
                "user_id" => $user->lv2_superior_id,
                "pay_uid" => $user_id,
                "item_id" => $item_id,
                "pay_amount" => $item->price,
                "back_amount" => sprintf("%.2f", $item->price * $rate),
                "status" => 0,
                "log_type" => 3
            ]);
            $cashback->receive();
        }
        // 上上上级用户返现
        if ($user->lv3_superior_id) {
            $rate_pec = setting("ITEM_LV3_SUPERIOR_CASHBACK_RATE", 5);
            $rate = floatval($rate_pec) / 100;
            // 为上上上级返现
            $cashback = UserCashback::create([
                "user_id" => $user->lv3_superior_id,
                "pay_uid" => $user_id,
                "item_id" => $item_id,
                "pay_amount" => $item->price,
                "back_amount" => sprintf("%.2f", $item->price * $rate),
                "status" => 0,
                "log_type" => 3
            ]);
            $cashback->receive();
        }
        
        
        // // 上级用户返现
        // if ($user->lv1_superior_id) {
        //     //查询上级今天获得了几次
        //     $getcashback = UserCashback::where("user_id",$user->lv1_superior_id)
        //     ->whereDay("created_at",today())
        //     ->where("item_id",$item->id)
        //     ->count();
        //     if(empty($getcashback)||($getcashback%3==0)){
        //          // 为上级返现
        //     $cashback = UserCashback::create([
        //         "user_id" => $user->lv1_superior_id,
        //         "pay_uid" => $user_id,
        //         "item_id" => $item_id,
        //         "pay_amount" => $item->price,
        //         "back_amount" => $item->cashback,
        //         "status" => 0,
        //         "log_type" => 3
        //     ]);
        //     }else if($getcashback==1||($getcashback%3==1)){
        //         $cashback = UserCashback::create([
        //         "user_id" => $user->lv1_superior_id,
        //         "pay_uid" => $user_id,
        //         "item_id" => $item_id,
        //         "pay_amount" => $item->price,
        //         "back_amount" => $item->cashback_two,
        //         "status" => 0,
        //         "log_type" => 3
        //     ]);
        //     }else{
        //         $cashback = UserCashback::create([
        //         "user_id" => $user->lv1_superior_id,
        //         "pay_uid" => $user_id,
        //         "item_id" => $item_id,
        //         "pay_amount" => $item->price,
        //         "back_amount" => $item->cashback_three+100,
        //         "status" => 0,
        //         "log_type" => 3
        //     ]);
        //     }
           
        //     $cashback->receive();
        // }
    }
    // 余额参与团购
    public function groupPurchaseItem($item_id, $user_id) {
        $item = $this->find($item_id);

        $user_coupon_id = request("user_coupon_id", 0);

        $userRepository = app(UserRepository::class);
        $gpRepository = app(GroupPurchaseRepository::class);

        // 检查是否开始
        if ($item->gp_start_time > now()) {
            throw new \Exception("item sold out");
        }

        // 检查是否结束
        if ($item->gp_end_time < now()) {
            throw new \Exception("item sold out");
        }
        

        // 检查是否已经加入
        $is_join = GroupPurchaseRecord::where("status", 0)
            ->where("expired_at", ">=", now())
            ->where("item_id", $item_id)
            ->where("user_id", $user_id)
            ->exists();
        if ($is_join) {
            throw new \Exception("already join this group purchase team");
        }

        // 20230626: 更换为可控参与人数
        // if ($item->joined_count >= $item->group_people_count) {
        if ($item->joined_count_display >= $item->group_people_count) {
            throw new \Exception("the number of people is full");
        }

        // 获取购买限制刷新时间点
        $time_limit = setting("STAT_TIME_LIMIT", "02:00");

        // 检查购买数量
        $ownerCount = UserItem::where("item_id", $item_id)
            ->where("user_id", $user_id)
            // ->where("earning_end_at", ">=", now())
            // ->where("created_at", ">=", now()->previous($time_limit))
            // ->where("created_at", "<", now()->next($time_limit))
            ->sum("amount");
        if ($ownerCount >= $item->purchase_limit) {
            throw new \Exception("exceeding the purchase quantity limit");
        }

        // 检查优惠券
        if ($user_coupon_id) {
            $user_coupon = UserCoupon::where("id", $user_coupon_id)->first();
            if (empty($user_coupon)) {
                throw new \Exception("user coupon id not exists");
            }

            if (now() > $user_coupon->expire_at) {
                throw new \Exception("coupon is expired");
            }

            if ($user_coupon->status == 1) {
                throw new \Exception("coupon is used");
            }
        }

        // 计算sn
        // $maxSn = UserItem::where("item_id", $item_id)
        //     ->where("user_id", $user_id)
        //     ->max("serial_number");
        // $sn = intval($maxSn) + 1;

        $buyAmount = 0;
        DB::beginTransaction();
        try {
            // 参与拼团
            $buyAmount = $gpRepository->join($user_id, $item_id);
            // // 添加拥有记录
            // UserItem::create([
            //     "user_id" => $user_id,
            //     "item_id" => $item_id,
            //     "earning_end_at" => now()->addDays($item->gain_day_num),
            //     "serial_number" => $sn,
            //     "last_earning_at" => now(),
            // ]);
            
            $price = $item->price * $buyAmount;
            if ($user_coupon_id) {
                $user_coupon = UserCoupon::where("id", $user_coupon_id)
                    ->lockForUpdate()
                    ->first();
                $price = $price * floatval($user_coupon->coupon->discount);
                // 将优惠券标记为已使用
                $user_coupon->status = 1;
                $user_coupon->item_id = $item_id;
                $user_coupon->save();
            }

            // 扣除余额
            $userRepository->addBalance([
                "user_id" => $user_id,
                "money" => 0 - $price,
                "log_type" => 6,
                "item_id" => $item_id,
                // 20230614: 使用可提现余额
                "balance_type" => "balance",
            ]);
            // if ($user->lv1_superior_id && $sn == 1) {
            //     // 为上级返现
            //     UserCashback::create([
            //         "user_id" => $user->lv1_superior_id,
            //         "pay_uid" => $user_id,
            //         "item_id" => $item_id,
            //         "pay_amount" => $item->price,
            //         "back_amount" => $item->cashback,
            //         "status" => 0,
            //         "log_type" => 3
            //     ]);
            // }

            

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw new \Exception($th->getMessage());
        }

        return $buyAmount;
    }

    // 售卖商品
    public function sale($item_id, $user_id) {
        $item = $this->find($item_id);
        // 预期售卖数量
        $amount = request("amount", 1);

        $userRepository = app(UserRepository::class);

        $user = $userRepository->find($user_id);

        // 检查售卖额度
        if ($item->remain_sales_amount <= 0) {
            throw new \Exception("there is no trade amount today");
        }
        // 如果剩余售卖额度小于用户要求售卖数量
        // 则仅售卖剩余额度数量的商品
        if ($item->remain_sales_amount < $amount) {
            $amount = $item->remain_sales_amount;
        }

        // 检查剩余可售卖数量
        $ownerCount = UserItem::where("user_id", $user->id)
            ->where("item_id", $item->id)
            ->sum("amount");
        if ($ownerCount <= 0) {
            throw new \Exception("no products for sale");
        }
        if ($ownerCount < $amount) {
            $amount = $ownerCount;
        }

        // 获取售卖手续费
        $service_fee = floatval(
            setting("ITEM_SELL_SERVICE_FEE", 5)
        );

        DB::beginTransaction();
        try {
            $record = UserItem::where("user_id", $user->id)
                ->where("item_id", $item->id)
                ->orderBy("created_at", "desc")
                ->first();
            
            if ($record->amount - $amount <= 0) {
                $record->delete();
            } else {
                $record->amount -= $amount;
                $record->save();
            
            }
            
            $item = Item::where("id", $item_id)
                ->lockForUpdate()
                ->first();
            // 添加库存
            $item->stock += $amount;
            // 减少可售卖额度
            $item->remain_sales_amount -= $amount;
            $item->save();

            // 扣除售卖手续费
            $sell_price = ($item->price * $amount) * (1 - ($service_fee / 100));

            // 添加余额
            $userRepository->addBalance([
                "user_id" => $user->id,
                "money" => $sell_price,
                "log_type" => 18,
                "item_id" => $item->id,
                // 20230614: 使用可提现余额
                "balance_type" => "balance",
            ]);
            
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw new \Exception("sell error");
        }

        return [
            "sell_amount" => $amount,
            "sell_price" => strval($sell_price),
        ];
    }

    // 领取商品收益
    public function gainItemEarning($user_item_id) {
        $record = UserItem::where("id", $user_item_id)->first();
        if (empty($record)) {
            throw new \Exception("record not exists");
        }
        $lock = Cache::lock('GAIN_ITEM_EARNING:' . $user_item_id, 10);
        try {
            $lock->block(5);

            if ($record->is_stoped==1) {
                throw new \Exception("The current product has suspended revenue, please contact customer service.");
            }
    
            if ($record->earning_status == 0) {
                throw new \Exception("You can only receive the benefits the next day");
            }
    
            if ($record->earning_status == 2) {
                throw new \Exception("You have already received the income");
            }
    
            $earning_end_at = new Carbon($record->earning_end_at);

            // 20230810: 针对设置了收益到期领取收益类型的商品进行处理
            if ($record->item->is_earning_at_end == 1) {
                if (!now()->greaterThanOrEqualTo($earning_end_at)) {
                    throw new \Exception("The earning has not yet time out");
                }
            }
    
            
            if (now()->greaterThanOrEqualTo($earning_end_at)) {
                // 最后一期
                ItemEarningManual::dispatch($record->id, true);
            } else {
                // 非最后一期
                ItemEarningManual::dispatch($record->id, false);
            }
        } catch (LockTimeoutException $e) {
            throw new \Exception("duplicate request, retry again please.");
        } finally {
            $lock?->release();
        }
    }
    
}
