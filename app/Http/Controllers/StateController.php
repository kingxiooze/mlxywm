<?php

namespace App\Http\Controllers;

use App\Models\MoneyLog;
use App\Models\Order;
use App\Models\User;
use App\Models\UserWithdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class StateController extends Controller
{
    // 平台统计信息
    public function getPlatformInfo(Request $request) {
        // 今日充值到账金额
        $t_o_money = Order::whereDate("created_at", today())
            ->where('pay_status', 1)
            ->whereIn('order_status', [2, 4])
            ->sum("money");

        // 今日提现金额
        $t_w_money = UserWithdrawal::where("pay_status", 1)
            ->where("order_status", 1)
            ->whereDate("pay_time", today())
            // ->where("pay_type", "!=", "2");
            ->sum("amount");

        // 总充值
        $r_s = Order::where("pay_status", 1)
            ->whereIn("order_status", [2, 4])
            // ->where("pay_type", "!=", 2);
            ->sum("money");

        // 总提现
        $w_s = UserWithdrawal::where("pay_status", 1)
            ->where("order_status", 1)
            // ->where("pay_type", "!=", 2);
            ->sum("amount");

        return $this->success([
            "today_recharge" => $t_o_money,
            "today_withdrawal" => $t_w_money,
            "total_recharge" => $r_s,
            "total_withdrawal" => $w_s,
        ]);

        
    }

    // // 获取所有用户成功的提现记录
    public function getSuccessWithdrawalList(){
        $paginate = UserWithdrawal::select(
                "id", "user_id", "status", "amount", 
                "bankcard_id", "created_at", "pay_time"
            )
            ->with(["user:id,name,avatar,mobile"])
            ->where("pay_status", 1)
            ->where("order_status", 1)
            ->orderBy("created_at", "desc")
            ->paginate(10);
        $paginate->getCollection()->transform(function ($value) {
            if ($value->pay_type == 2) {
                $value->pay_type = "usdt";
            } else {
                $value->pay_type = "bank";
            }

            $value->status = 1;
            unset($value->pay_status);
            unset($value->order_status);

            $value->user->mobile = Str::mask($value->user->mobile, "*", 3, 5);

            return $value;
        });
        return $this->success($paginate);
    }

    // 获取所有用户成功的充值记录
    public function getSuccessRechargeList(){
        $paginate = Order::select(
                "id", "user_id", "order_no", "pay_type", "price", 
                "created_at", "pay_time", "pay_status", "order_status"
            )
            ->with(["user:id,name,avatar,mobile"])
            ->where("pay_status", 1)
            ->whereIn("order_status", [2, 4])
            ->orderBy("created_at", "desc")
            ->paginate(10);
        $paginate->getCollection()->transform(function ($value) {
            if ($value->pay_type == 2) {
                $value->pay_type = "usdt";
            } else {
                $value->pay_type = "bank";
            }

            $value->status = 1;
            unset($value->pay_status);
            unset($value->order_status);

            $value->user->mobile = Str::mask($value->user->mobile, "*", 3, 5);

            return $value;
        });
        return $this->success($paginate);
    }

    // 邀请充值排行榜(LB=leader board=排行榜)
    public function getInviteRechargeLB() {
        // SELECT id, `name`, `avatar`, 
	    //     (SELECT SUM(money) FROM `money_log` WHERE log_type=1 AND user_id IN 
        //         (SELECT id FROM users AS user_b 
        //             where user_a.id = user_b.lv1_superior_id)
        //     ) as recharge_amount,
        //     (SELECT count(id) FROM users AS user_b 
        //         where user_a.id = user_b.lv1_superior_id) AS invite_count
        // FROM users AS user_a ORDER BY recharge_amount desc;

        // 2023-08-01 00:00,2023-09-06 00:00
        $datetime_range = setting("INVITE_RECHARGE_DATETIME_RANGE", null);
        $invite_limit = setting("INVITE_RECHARGE_INVITE_LIMIT", null);

        // 下级用户ID子查询
        $sid_sq = DB::table("users AS user_b")
            ->select("id")
            ->whereRaw("`user_a`.`id` = `user_b`.`lv1_superior_id`")
            ->whereNull("user_b.deleted_at");

        // 下级用户数量子查询
        $scount_sq = DB::table("users AS user_b")
            ->selectRaw("count(id)")
            ->whereRaw("`user_a`.`id` = `user_b`.`lv1_superior_id`")
            ->whereNull("user_b.deleted_at");

        // 充值金额子查询
        $srecharge_sq = DB::table("money_log")
            ->selectRaw("SUM(money)")
            ->where("log_type", 1)
            ->whereIn("user_id", $sid_sq);

        if (!empty($datetime_range)) {
            $datetimes = explode(",", $datetime_range);
            $srecharge_sq = $srecharge_sq
                ->where("created_at", ">=", $datetimes[0])
                ->where("created_at", "<=", $datetimes[1]);
        }

        // 总查询
        $query = DB::table("users as user_a")
            // 20230925: 里面给手机号 ，手机号需要中间加*
            ->selectRaw("id, name, avatar, mobile")
            ->orderBy("recharge_amount", "desc")
            ->orderBy("invite_count", "desc")
            ->whereNull("user_a.deleted_at")
            ->addSelect([
                "recharge_amount" => $srecharge_sq,
                "invite_count" => $scount_sq,
            ]);

        if (!empty($invite_limit)) {
            $query = $query->having("invite_count", ">", $invite_limit);
        }
        
        $result = $query->limit(10)->get();
        // 20230925: 里面给手机号 ，手机号需要中间加*
        $result->transform(function ($value) {
            $value->mobile = Str::mask($value->mobile, "*", 3, 5);

            return $value;
        });
        return $this->success($result);



        
    }
}
