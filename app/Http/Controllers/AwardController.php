<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\UserCashback;
use App\Repositories\RedPackRepository;
use Illuminate\Http\Request;
use App\Repositories\UserRepository;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class AwardController extends Controller
{

    // 返现首页数据
    public function getAwardList(Request $request) {
        $user = auth()->user();
        $invite_limit = (int) setting('REWARD_INVITE_USER_COUNT');
        if ($user->total_invite >= $invite_limit) {
            $total_invite = $invite_limit;
        } else {
            $total_invite = $user->total_invite;
        }
        $data = [
            "total_invite" => $total_invite,
            "received_invite" => $user->received_invite,
            "unreceive_invite" => $total_invite - $user->received_invite,
            "invite_limit" => $invite_limit,
            "invite_reward" => (int) setting('INVITE_USER_REWARD'),
            "rewards" => []
        ];

        $items = Item::orderBy("price", "asc")
            ->where("is_sell", 1)
            ->get();
        foreach ($items as $item) {
            $r = UserCashback::where("user_id", $user->id)
                ->where("item_id", $item->id)
                ->select("status", DB::raw("count(id) as c"))
                ->groupBy("status")
                ->get()
                ->pluck("c", "status")
                ->toArray();
            array_push($data["rewards"], 
                array_merge(
                    $item->toArray(), 
                    [
                        "unreceive" => Arr::get($r, "0", 0),
                        "received" => Arr::get($r, "1", 0)
                    ]
                )
            );
        }

        return $this->success($data);
    }

    // // 领取邀请返现
    // public function postReceiveInvite(Request $request) {
    //     $user = auth()->user();
    //     $invite_limit = (int) setting('REWARD_INVITE_USER_COUNT');
    //     $invite_reward = (int) setting('INVITE_USER_REWARD');

    //     // 计算可以获得奖励的上线
    //     if ($user->total_invite >= $invite_limit) {
    //         $total_invite = $invite_limit;
    //     } else {
    //         // $total_invite = $user->total_invite;
    //         return $this->errorBadRequest("three people must be invited");
    //     }

    //     // 如果上限比已经领取的多，则返回
    //     // (例如调低了上限)
    //     // 2023-05-25: 去掉了限制
    //     // if ($user->received_invite >= $total_invite) {
    //     //     return $this->errorBadRequest("receive complete");
    //     // }

    //     // 计算实际可领取的数量
    //     $available_invite = $total_invite - $user->received_invite;
    //     if ($available_invite > 0) {
    //         $reward = $available_invite * $invite_reward;
    //         DB::beginTransaction();
    //         try {
    //             // 用户添加余额
    //             $userRepository = app(UserRepository::class);
    //             $userRepository->addBalance([
    //                 "user_id" => $user->id,
    //                 "money" => $reward,
    //                 "log_type" => 7,
    //                 "balance_type" => "mission_balance"
    //             ]);
    //             // 更新用户邀请信息
    //             $user->received_invite += $available_invite;
    //             $user->unreceive_invite -= $available_invite;
    //             $user->save();
    //             DB::commit();
    //         } catch (\Throwable $th) {
    //             DB::rollBack();
    //             return $this->errorBadRequest("receive error");
    //         }

    //         $rpRepository = app(RedPackRepository::class);
    //         $rpRepository->withdrawalRedpacketBalance($user->id);
    //     }

    //     return $this->success([
    //         "total_invite" => $total_invite,
    //         "received_invite" => $user->received_invite,
    //         "unreceive_invite" => $total_invite - $user->received_invite,
    //     ]);
        
    // }

    // 领取邀请返现
    // 20230919: 这个接口改成每邀请一个领取一个用户的奖励。 直接复制现在amd的逻辑
    public function postReceiveInvite(Request $request) {
        $user = auth()->user();
        $invite_limit = (int) setting('REWARD_INVITE_USER_COUNT');
        $invite_reward = (int) setting('INVITE_USER_REWARD');

        // 计算可以获得奖励的上线
        if ($user->total_invite >= $invite_limit) {
            $total_invite = $invite_limit;
        } else {
            // 20230821: 还原成每邀请一个人领取奖励的版本
            $total_invite = $user->total_invite;
            // return $this->errorBadRequest($invite_limit . " people must be invited");
        }

        // 20230822: 邀请用户获得收益这个要统计被邀请用户是否有购买商品，如果没有购买商品的用户不算
        // 20230825: 还原
        // 计算有购买过商品的邀请用户数量
        // $ownitem_invite = User::where("lv1_superior_id", $user->id)
        //     ->has("own_item", ">", "0")
        //     ->count();
        // 如果购买过商品的邀请用户数量比邀请人数少，则按照购买过商品的邀请用户数量来计算
        // 不考虑更多的情况，因为更多时，必定超过了可领取上限
        // if ($ownitem_invite < $total_invite) {
        //     $total_invite = $ownitem_invite;
        // }

        // 如果上限比已经领取的多，则返回
        // (例如调低了上限)
        // 2023-05-25: 去掉了限制
        // 20230817: 需要类似功能，接口重新启用
        if ($user->received_invite >= $total_invite) {
            // return $this->errorBadRequest("receive fail");
            // 20230920: receive fail  改成  Received, please continue to invite.
            return $this->errorBadRequest("Received, please continue to invite.");
            
        }

        // 计算实际可领取的数量
        $available_invite = $total_invite - $user->received_invite;
        if ($available_invite > 0) {
            $reward = $available_invite * $invite_reward;
            $lock = Cache::lock("INVITE_CASHBACK_RECEIVE:" . $user->id, 10);
            DB::beginTransaction();
            try {
                $lock->block(5);

                // 用户添加余额
                $userRepository = app(UserRepository::class);
                $userRepository->addBalance([
                    "user_id" => $user->id,
                    "money" => $reward,
                    "log_type" => 7,
                    "balance_type" => "balance"
                ]);
                // 更新用户邀请信息
                $user->received_invite += $available_invite;
                $user->unreceive_invite -= $available_invite;
                $user->save();
                DB::commit();

            } catch (LockTimeoutException $e) {
                return $this->errorBadRequest("duplicate request, retry again please.");
            } catch (\Throwable $th) {
                return $this->errorBadRequest("receive error");
            } finally {
                DB::rollBack();
                $lock?->release();
            }

            // $rpRepository = app(RedPackRepository::class);
            // $rpRepository->withdrawalRedpacketBalance($user->id);
        }

        return $this->success([
            "total_invite" => $total_invite,
            "received_invite" => $user->received_invite,
            "unreceive_invite" => $total_invite - $user->received_invite,
        ]);
        
    }

    // 领取商品返现
    public function postReceiveItem(Request $request) {
        $item_id = $request->input("item_id", 0);

        $userRepository = app(UserRepository::class);
        
        $user = auth()->user();
        $cashbacks = UserCashback::where("user_id", $user->id)
            ->where("item_id", $item_id)
            ->where("status", 0)
            ->get();
        foreach ($cashbacks as $cashback) {
            $back_amount = $cashback->back_amount;
            if (floatval($back_amount) > 0) {
                DB::beginTransaction();
                try {
                    $log_type = $cashback->log_type ?? 3;
                    // 用户添加余额
                    $userRepository->addBalance([
                        "user_id" => $user->id,
                        "money" => $back_amount,
                        "log_type" => $log_type,
                        "item_id" => $cashback->item_id,
                        "source_uid" => $cashback->pay_uid
                    ]);
                    // 更新返现领取状态
                    $cashback->status = 1;
                    $cashback->save();
                    DB::commit();
                } catch (\Throwable $th) {
                    DB::rollBack();
                    return $this->errorBadRequest("receive error");
                }
            }
        }
        
        

        // refreash
        $r = UserCashback::where("user_id", $user->id)
            ->where("item_id", $item_id)
            ->select("status", DB::raw("count(id) as c"))
            ->groupBy("status")
            ->get()
            ->pluck("c", "status")
            ->toArray();
        return $this->success([
            "item_id" => $item_id,
            "unreceive" => Arr::get($r, "0", 0),
            "received" => Arr::get($r, "1", 0)
        ]);
        
    }

    // 查询充值返现金额
    public function getRechargeCashbackStat(Request $request) {
        $user = auth()->user();
        
        $back_amount = UserCashback::where("user_id", $user->id)
            ->where("item_id", 0)
            ->where("log_type", 19)
            ->where("back_amount", ">", 0)
            ->where("status", 0)
            ->sum("back_amount");
        return $this->success([
            "back_amount" => $back_amount
        ]);
    }

    // 领取充值返现
    public function postRechargeCashbackReceive(Request $request) {
        $user = auth()->user();

        $userRepository = app(UserRepository::class);

        $cashbacks = UserCashback::where("user_id", $user->id)
            ->where("item_id", 0)
            ->where("status", 0)
            ->where("back_amount", ">", 0)
            ->where("log_type", 19)
            ->get();
        $receive_amount = 0;
        foreach ($cashbacks as $cashback) {
            $back_amount = $cashback->back_amount;
            if (floatval($back_amount) > 0) {
                DB::beginTransaction();
                try {
                    $log_type = $cashback->log_type ?? 19;
                    // 用户添加余额
                    $userRepository->addBalance([
                        "user_id" => $user->id,
                        "money" => $back_amount,
                        "log_type" => $log_type,
                        "source_uid" => $cashback->pay_uid
                    ]);
                    // 更新返现领取状态
                    $cashback->status = 1;
                    $cashback->save();
                    DB::commit();
                    $receive_amount += $back_amount;
                } catch (\Throwable $th) {
                    DB::rollBack();
                    return $this->errorBadRequest("receive error");
                }
            }
        }

        return $this->success([
            "receive_amount" => strval($receive_amount)
        ]);
    }

    // 查询充值返现记录
    public function getRechargeCashbackLog(Request $request) {
        $user = auth()->user();
        
        $paginate = UserCashback::where("user_id", $user->id)
            ->where("item_id", 0)
            ->where("log_type", 19)
            ->with("pay_user:id,name,avatar")
            ->orderBy("created_at", "desc")
            // ->where("status", 0)
            ->paginate(15);
        return $this->success($paginate);
    }
}
