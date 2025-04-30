<?php

namespace App\SalesAdmin\Controllers;

use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Http\Controllers\AdminController;
use App\Models\User;
use App\Models\UserItem;
use App\Models\MoneyLog;
use App\Models\Order;
use App\Models\UserWithdrawal;
use Dcat\Admin\Admin;

class StateController extends AdminController
{

    protected $subordinates;

    public function index(Content $content){

        $subordinates = Admin::user()->subordinates();
        $this->subordinates = $subordinates;

        $content->row(function (Row $row) {
            $row->column(3, $this->card('today_platform_profit'));
            $row->column(3, $this->card('today_user_count'));
            // $row->column(3, $this->card('today_turnover'));
            // $row->column(3, $this->card('today_transaction_amount'));
            $row->column(3, $this->card('today_withdrawal_money'));
            //$row->column(3, $this->card('today_withdrawal_usdt'));
        });

        $content->row(function (Row $row) {
            $row->column(3, $this->card('total_platform_profit', true));
            $row->column(3, $this->card('total_user_count', true));
            // $row->column(3, $this->card('total_turnover', true));
            // $row->column(3, $this->card('total_transaction_amount', true));
            $row->column(3, $this->card('total_withdrawal_money', true));
            //$row->column(3, $this->card('total_withdrawal_usdt', true));
        });

        $content->row(function (Row $row) {
            $row->column(3, $this->card('today_recharge_money'));
            //$row->column(3, $this->card('today_recharge_usdt'));
            //$row->column(3, $this->card('today_recharge_user_count'));
            
        });

        $content->row(function (Row $row) {
            $row->column(3, $this->card('total_recharge_money', true));
            //$row->column(3, $this->card('total_recharge_usdt', true));
            //$row->column(3, $this->card('total_recharge_user_count', true));
            
        });

        // $content->row(function (Row $row) {
        // });
        // $content->row(function (Row $row) {
        // });


        // $desc = $this->getTodayActiveCount();

        return $content->header('data statistics');
    }

    /**
     * @param $text
     * @param string $color
     * @return string
     */
    protected function card($cb_name, $is_second_row=false)
    {
        $text = $this->$cb_name();
        if ($is_second_row) {
            $margin_bottom = "margin-bottom:22px;";
        } else {
            $margin_bottom = "";
        }
        return <<<EOF
<div style="background:#fff;padding:10px 22px 16px;box-shadow:0 1px 3px 1px rgba(34, 25, 25, 0.1);$margin_bottom">
    <div style="">
        $text
    </div>
</div>
EOF;
    }

    // 平台利润
    public function today_platform_profit(){
        // $t_p = MoneyLog::whereDate("created_at", today())
        //     ->whereIn("log_type", [1, 2, 18])
        //     ->whereIn("user_id", $this->subordinates)
        //     ->sum("money");
        // $y_p = MoneyLog::whereDate("created_at", today()->subDay())
        //     ->whereIn("log_type", [1, 2, 18])
        //     ->whereIn("user_id", $this->subordinates)
        //     ->sum("money");

        // 今日充值到账金额
        $t_o_money = Order::whereDate("created_at", today())
            ->where('pay_status', 1)
            ->whereIn('order_status', [2, 4])
            ->whereIn("user_id", $this->subordinates)
            ->sum("money");
        // 今日提现到账扣除完手续费的金额
        $t_w_price = UserWithdrawal::whereDate("pay_time", today())
            ->where('pay_status', 1)
            ->where('order_status', 1)
            ->whereIn("user_id", $this->subordinates)
            ->sum("amount");
        // 今日平台利润
        $t_p = $t_o_money - $t_w_price;

        // 昨日充值到账金额
        $y_o_money = Order::whereDate("created_at", today()->subDay())
            ->where('pay_status', 1)
            ->whereIn('order_status', [2, 4])
            ->whereIn("user_id", $this->subordinates)
            ->sum("money");
        // 昨日提现到账扣除完手续费的金额
        $y_w_price = UserWithdrawal::whereDate("pay_time", today()->subDay())
            ->where('pay_status', 1)
            ->where('order_status', 1)
            ->whereIn("user_id", $this->subordinates)
            ->sum("amount");
        // 昨日平台利润
        $y_p = $y_o_money - $y_w_price;

        return <<<EOF
Platform profit<br/>
$t_p<br/>
昨日:$y_p<br/>
EOF;
    }

    // 今日会员数
    public function today_user_count(){
        $today_user = User::whereDate("created_at", today())
            ->whereIn("id", $this->subordinates)
            ->count();
        $yestday_user = User::whereDate("created_at", today()->subDay())
            ->whereIn("id", $this->subordinates)
            ->count();
        return <<<EOF
Number of members today<br/>
$today_user<br/>
yesterday:$yestday_user<br/>
EOF;
    }

    // 今日成交量
    public function today_turnover(){
        $today_turnover = MoneyLog::whereDate("created_at", today())
            ->where("log_type", 6)
            ->whereIn("user_id", $this->subordinates)
            ->count();
        $yestday_turnover = MoneyLog::whereDate("created_at", today()->subDay())
            ->where("log_type", 6)
            ->whereIn("user_id", $this->subordinates)
            ->count();
        return <<<EOF
 <br/>
$today_turnover<br/>
yesterday:$yestday_turnover<br/>
EOF;
    }

    // 今日交易金额
    public function today_transaction_amount(){
        $today_amount = MoneyLog::whereDate("created_at", today())
            ->where("log_type", 6)
            ->whereIn("user_id", $this->subordinates)
            ->sum("money");
        $yestday_amount = MoneyLog::whereDate("created_at", today()->subDay())
            ->where("log_type", 6)
            ->whereIn("user_id", $this->subordinates)
            ->sum("money");
        return <<<EOF
Today's trading amount(Rs)<br/>
$today_amount<br/>
yesterday:$yestday_amount<br/>
EOF;
    }

    // 总利润
    public function total_platform_profit(){
        // 总提现
        $w_query = UserWithdrawal::where("pay_status", 1)
            ->where("order_status", 1)
            // ->where("pay_type", "!=", 2)
            ->whereIn("user_id", $this->subordinates);
        $w_s = $w_query->sum("amount");

        // 总充值
        $r_query = Order::where("pay_status", 1)
            ->whereIn("order_status", [2, 4])
            ->where("goods_type", "1")
            // ->where("pay_type", "!=", 2)
            ->whereIn("user_id", $this->subordinates);
        $r_s = $r_query->sum("money");

        $t_p = $r_s - $w_s;
        return <<<EOF
gross profit
<br/>
$t_p<br/>
EOF;
    }

    // 会员总数
    public function total_user_count(){
        $total_user = User::whereIn("id", $this->subordinates)
            ->count();
        return <<<EOF
Total membership<br/>
$total_user<br/>
EOF;
    }

    // 总单量
    public function total_turnover(){
        $total_turnover = MoneyLog::whereDate("created_at", today())
            ->where("log_type", 6)
            ->whereIn("user_id", $this->subordinates)
            ->count();
        return <<<EOF
总单量<br/>
$total_turnover<br/>
EOF;
    }

    // 总交易
    public function total_transaction_amount(){
        $total_amount = MoneyLog::whereDate("created_at", today())
            ->where("log_type", 6)
            ->whereIn("user_id", $this->subordinates)
            ->sum("money");
        return <<<EOF
总交易<br/>
$total_amount<br/>
EOF;
    }

    // 今日充值钱
    public function today_recharge_money(){
        $t_query = Order::where("pay_status", 1)
            ->whereIn("order_status", [2, 4])
            ->whereDate("pay_time", today())
            ->where("goods_type", "1")
            // ->where("pay_type", "!=", 2)
            ->whereIn("user_id", $this->subordinates);
        $t_s = $t_query->sum("money");
        $t_c = $t_query->count();

        $y_query = Order::where("pay_status", 1)
            ->whereIn("order_status", [2, 4])
            ->whereDate("pay_time", today()->subDay())
            ->where("goods_type", "1")
            // ->where("pay_type", "!=", 2)
            ->whereIn("user_id", $this->subordinates);
        $y_s = $y_query->sum("money");
        $y_c = $y_query->count();

        return <<<EOF
deposit today<br/>
$t_s(quantity:$t_c)<br/>
yesterday:$y_s(quantity:$y_c)<br/>
EOF;
    }

    // 今日充值usdt
    public function today_recharge_usdt(){
        $t_query = Order::where("pay_status", 1)
            ->whereIn("order_status", [2, 4])
            ->whereDate("pay_time", today())
            ->where("goods_type", "1")
            ->where("pay_type", 2)
            ->whereIn("user_id", $this->subordinates);
        $t_s = $t_query->sum("price");
        $t_c = $t_query->count();

        $y_query = Order::where("pay_status", 1)
            ->whereIn("order_status", [2, 4])
            ->whereDate("pay_time", today()->subDay())
            ->where("goods_type", "1")
            ->where("pay_type", 2)
            ->whereIn("user_id", $this->subordinates);
        $y_s = $y_query->sum("price");
        $y_c = $y_query->count();
        return <<<EOF
deposit today(U)<br/>
$t_s(quantity:$t_c)<br/>
yesterday:$y_s(quantity:$y_c)<br/>
EOF;
    }

    // 今日提现钱
    public function today_withdrawal_money(){
        $t_query = UserWithdrawal::where("pay_status", 1)
            ->where("order_status", 1)
            ->whereDate("pay_time", today())
            ->where("pay_type", "!=", 2)
            ->whereIn("user_id", $this->subordinates);
        $t_s = $t_query->sum("amount");
        $t_c = $t_query->count();

        $y_query = UserWithdrawal::where("pay_status", 1)
            ->where("order_status", 1)
            ->whereDate("pay_time", today()->subDay())
            ->where("pay_type", "!=", 2)
            ->whereIn("user_id", $this->subordinates);
        $y_s = $y_query->sum("amount");
        $y_c = $y_query->count();
        return <<<EOF
Withdrawal today(Rs)<br/>
$t_s(quantity:$t_c)<br/>
yesterday:$y_s(quantity:$y_c)<br/>
EOF;
    }

    // 今日提现usdt
    public function today_withdrawal_usdt(){
        $t_query = UserWithdrawal::where("pay_status", 1)
            ->where("order_status", 1)
            ->whereDate("pay_time", today())
            ->where("pay_type", 2)
            ->whereIn("user_id", $this->subordinates);
        $t_s = $t_query->sum("amount");
        $t_c = $t_query->count();

        $y_query = UserWithdrawal::where("pay_status", 1)
            ->where("order_status", 1)
            ->whereDate("pay_time", today()->subDay())
            ->where("pay_type", 2)
            ->whereIn("user_id", $this->subordinates);
        $y_s = $y_query->sum("amount");
        $y_c = $y_query->count();
        return <<<EOF
今日提现(U)<br/>
$t_s(quantity:$t_c)<br/>
昨日:$y_s(quantity:$y_c)<br/>
EOF;
    }

    // 总充值钱
    public function total_recharge_money(){
        $t_query = Order::where("pay_status", 1)
            ->whereIn("order_status", [2, 4])
            ->where("goods_type", "1")
            // ->where("pay_type", "!=", 2)
            ->whereIn("user_id", $this->subordinates);
        $t_s = $t_query->sum("money");
        $t_c = $t_query->count();
        return <<<EOF
Total Amount of deposit<br/>
$t_s(quantity:$t_c)<br/>
EOF;
    }

    // 总充值usdt
    public function total_recharge_usdt(){
        $t_query = Order::where("pay_status", 1)
            ->whereIn("order_status", [2, 4])
            ->where("goods_type", "1")
            ->where("pay_type", 2)
            ->whereIn("user_id", $this->subordinates);
        $t_s = $t_query->sum("price");
        $t_c = $t_query->count();
        return <<<EOF
总充值(U)<br/>
$t_s(quantity:$t_c)<br/>
EOF;
    }

    // 总提现钱
    public function total_withdrawal_money(){
        $t_query = UserWithdrawal::where("pay_status", 1)
            ->where("order_status", 1)
            ->where("pay_type", "!=", 2)
            ->whereIn("user_id", $this->subordinates);
        $t_s = $t_query->sum("amount");
        $t_c = $t_query->count();
        return <<<EOF
Total withdrawal(Rs)<br/>
$t_s(quantity:$t_c)<br/>
EOF;
    }

    // 总提现usdt
    public function total_withdrawal_usdt(){
        $t_query = UserWithdrawal::where("pay_status", 1)
            ->where("order_status", 1)
            ->where("pay_type", 2)
            ->whereIn("user_id", $this->subordinates);
        $t_s = $t_query->sum("amount");
        $t_c = $t_query->count();
        return <<<EOF
总提现(U)<br/>
$t_s(quantity:$t_c)<br/>
EOF;
    } 

    // 今日活跃度
    public function getTodayActiveCount() {
        // $records = UserItem::whereDate("created_at", today())
        //     // ->where("status", 2)
        //     ->whereIn("user_id", $this->subordinates)
        //     ->selectRaw("count(id) as mc, user_id")
        //     ->groupBy("user_id")
        //     ->get();
        // $total_m = 0;
        // $total_u = 0;
        // $recharged_m = 0;
        // $recharged_u = 0;
        // $unrecharged_m = 0;
        // $unrecharged_u = 0;

        // foreach ($records as $record) {
        //     $is_recharge = MoneyLog::where("user_id", $record->user_id)
        //             ->where("log_type", 1)
        //             ->sum("money");
        //     if ($is_recharge) {
        //         $recharged_u += 1;
        //         $recharged_m += $record->mc;
        //     } else {
        //         $unrecharged_u += 1;
        //         $unrecharged_m += $record->mc;
        //     }
        //     $total_u += 1;
        //     $total_m += $record->mc;
        // }

        // return sprintf(
        //     "%s = %s + %s", 
        //     "<span style='color:red'>当日总活跃度" . $total_u . "</span>",
        //     "<span style='color:blue'>有效活跃度" . $recharged_u . "</span>",
        //     "<span style='color:DarkViolet'>体验活跃度" . $unrecharged_u . "</span>"
        // );

        $recharged_u = UserItem::whereDate("created_at", today())
            ->whereIn("user_id", $this->subordinates)
            ->has("user.recharge_log")
            ->pluck("user_id")
            ->unique()
            ->count();
        $unrecharged_u = UserItem::whereDate("created_at", today())
            ->whereIn("user_id", $this->subordinates)
            ->doesntHave("user.recharge_log")
            ->pluck("user_id")
            ->unique()
            ->count();
        $total_u = $recharged_u + $unrecharged_u;
        return sprintf(
            "%s = %s + %s", 
            "<span style='color:red'>当日总活跃度" . $total_u . "</span>",
            "<span style='color:blue'>有效活跃度" . $recharged_u . "</span>",
            "<span style='color:DarkViolet'>体验活跃度" . $unrecharged_u . "</span>"
        );
    }

    // 今日充值人数
    public function today_recharge_user_count() {
        $today = MoneyLog::whereDate("created_at", today())
            ->whereIn("user_id", $this->subordinates)
            ->where('log_type', "1")
            ->pluck("user_id")
            ->unique()
            ->count();
        return <<<EOF
deposits today(remeber)<br/>
$today<br/>
<br/>
EOF;
    }

    // 充值总人数
    public function total_recharge_user_count() {
        $total = MoneyLog::where('log_type', "1")
            ->whereIn("user_id", $this->subordinates)
            ->pluck("user_id")
            ->unique()
            ->count();
        return <<<EOF
Total deposits(remeber)<br/>
$total<br/>
EOF;
    }

}