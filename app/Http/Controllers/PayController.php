<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderUsdt;
use App\Models\UserBankcard;
use App\Models\UserWithdrawal;
use App\Models\UserItem;
use App\Models\TaskOrder;
use App\Models\TaskModel;
use Illuminate\Http\Request;
use App\Services\Payment\Tool as PaymentTool;
use App\Services\Payment\CSPay;
use App\Services\Payment\YTPay;
use App\Services\Payment\GSPay;
use App\Services\Payment\WEPay;
use App\Services\Payment\DFPay;
use App\Services\Payment\SharkPay;
use App\Services\Payment\GTPay;
use App\Services\Payment\PPay;
use App\Services\Payment\FFPay;
use App\Services\Payment\WOWPay;
use App\Services\Payment\MPay;
use App\Services\Payment\XDPay;
use App\Services\Payment\PTMPay;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PayController extends Controller
{
    
    //获取支付订单
    public function getOrderInfo(Request $request) {
        $user = auth()->user();
        //获取最新的未支付order
        $orderInfo = Order::where("user_id",$user->id)->where("pay_status",2)->orderBy("created_at","desc")->first();
        
        return $this->success($orderInfo);
        
    }
    
    // 创建充值订单
    public function postRecharge(Request $request) {
        $pay_type = $request->input("pay_type", "1");
        $number = $request->input("number", "10");
        //查询金额
        $TaskOrder = TaskOrder::where("number",$number)->first();
        
        $amount = $TaskOrder->price;
        $bankcard_id = $request->input("bankcard_id", null);
        
        if ($pay_type == "1") {
            // CSPAY
            // 20230914: 关闭
            return $this->errorForbidden("Not Support");
            $min_amount = intval(
                setting("CASH_PAY_MIN_AMOUNT", 10)
            );
            if (floatval($amount) < $min_amount) {
                return $this->errorBadRequest("minimum payment amount is " . $min_amount);
            }
        } elseif ($pay_type == "2") {
            // USDT
            // 20230914: 关闭
            return $this->errorForbidden("Not Support");
            $min_amount = intval(
                setting("USDT_PAY_MIN_AMOUNT", 6)
            );
            if (floatval($amount) < $min_amount) {
                return $this->errorBadRequest("minimum payment amount is " . $min_amount);
            }
        } elseif ($pay_type == "3") {
            // YTPAY
            // 20230914: 关闭
            return $this->errorForbidden("Not Support");
            $min_amount = intval(
                setting("CASH_PAY_MIN_AMOUNT", 10)
            );
            if (floatval($amount) < $min_amount) {
                return $this->errorBadRequest("minimum payment amount is " . $min_amount);
            }

            if (empty($bankcard_id)) {
                return $this->errorBadRequest("add bankcard first");
            }
        } elseif ($pay_type == "4") {
            // GSPAY
            // 20230914: 关闭
            // return $this->errorForbidden("Not Support");
            $min_amount = intval(
                setting("CASH_PAY_MIN_AMOUNT", 10)
            );
            if (floatval($amount) < $min_amount) {
                return $this->errorBadRequest("minimum payment amount is " . $min_amount);
            }

        } elseif ($pay_type == "5") {
            // WEPAY
            // 20230914: 关闭
            return $this->errorForbidden("Not Support");
            $min_amount = intval(
                setting("CASH_PAY_MIN_AMOUNT", 10)
            );
            if (floatval($amount) < $min_amount) {
                return $this->errorBadRequest("minimum payment amount is " . $min_amount);
            }

        } elseif ($pay_type == "6") {
            // DFPAY
            // 20230914: 关闭
            return $this->errorForbidden("Not Support");
            $min_amount = intval(
                setting("CASH_PAY_MIN_AMOUNT", 10)
            );
            if (floatval($amount) < $min_amount) {
                return $this->errorBadRequest("minimum payment amount is " . $min_amount);
            }
        } elseif ($pay_type == "7") {
            // SharkPAY
             
            $min_amount = intval(
                setting("CASH_PAY_MIN_AMOUNT", 10)
            );
            if (floatval($amount) < $min_amount) {
                return $this->errorBadRequest("minimum payment amount is " . $min_amount);
            }
        } elseif ($pay_type == "8") {
            // GTPAY
            // 20230914: 关闭
            return $this->errorForbidden("Not Support");
            $min_amount = intval(
                setting("CASH_PAY_MIN_AMOUNT", 10)
            );
            if (floatval($amount) < $min_amount) {
                return $this->errorBadRequest("minimum payment amount is " . $min_amount);
            }
        } elseif ($pay_type == "9") {
            // PPAY
            // 20230914: 关闭
            return $this->errorForbidden("Not Support");
            $min_amount = intval(
                setting("CASH_PAY_MIN_AMOUNT", 10)
            );
            if (floatval($amount) < $min_amount) {
                return $this->errorBadRequest("minimum payment amount is " . $min_amount);
            }
        } elseif ($pay_type == "10") {
            // MPay
            // 20230914: 关闭
            return $this->errorForbidden("Not Support");
            $min_amount = intval(
                setting("CASH_PAY_MIN_AMOUNT", 10)
            );
            if (floatval($amount) < $min_amount) {
                return $this->errorBadRequest("minimum payment amount is " . $min_amount);
            }
        } elseif ($pay_type == "11") {
            // FFPay
            $min_amount = intval(
                setting("CASH_PAY_MIN_AMOUNT", 10)
            );
            if (floatval($amount) < $min_amount) {
                return $this->errorBadRequest("minimum payment amount is " . $min_amount);
            }
        } elseif ($pay_type == "12") {
            // XDPay-X1
            // 20230914: 关闭
            return $this->errorForbidden("Not Support");
            $min_amount = intval(
                setting("CASH_PAY_MIN_AMOUNT", 10)
            );
            if (floatval($amount) < $min_amount) {
                return $this->errorBadRequest("minimum payment amount is " . $min_amount);
            }
        } elseif ($pay_type == "13") {
            // XDPay-DGM
            // 20230914: 关闭
            return $this->errorForbidden("Not Support");
            $min_amount = intval(
                setting("CASH_PAY_MIN_AMOUNT", 10)
            );
            if (floatval($amount) < $min_amount) {
                return $this->errorBadRequest("minimum payment amount is " . $min_amount);
            }
        } elseif ($pay_type == "14") {
            // XDPay-X2
            // 20230914: 关闭
            return $this->errorForbidden("Not Support");
            $min_amount = intval(
                setting("CASH_PAY_MIN_AMOUNT", 10)
            );
            if (floatval($amount) < $min_amount) {
                return $this->errorBadRequest("minimum payment amount is " . $min_amount);
            }
        } elseif ($pay_type == "15") {
            // WOWPay
            // 20230914 9:00 关闭
            // 20230914 10:30 新增
            // return $this->errorForbidden("Not Support");
            $min_amount = intval(
                setting("CASH_PAY_MIN_AMOUNT", 10)
            );
            if (floatval($amount) < $min_amount) {
                return $this->errorBadRequest("minimum payment amount is " . $min_amount);
            }
        } elseif ($pay_type == "16") {
            // PTMPay
            // 20230914: 关闭
            return $this->errorForbidden("Not Support");
            $min_amount = intval(
                setting("CASH_PAY_MIN_AMOUNT", 10)
            );
            if (floatval($amount) < $min_amount) {
                return $this->errorBadRequest("minimum payment amount is " . $min_amount);
            }
        }

        $user = auth()->user();

        $outTradeNo = PaymentTool::generateOutTradeNo();
        $order = Order::create([
            "order_no" => $outTradeNo,
            "user_id" => $TaskOrder->user_id,
            "pay_type" => $pay_type,
            "goods_type" => 1,
            "pay_status" => 2,
            "order_status" => 1,
            "price" => $amount
        ]);
        
        $errMsg = "Temporarily closed, please use another payment method.";
        if ($pay_type == "1") {
            // CSPAY
            $service = new CSPay();
            try {
                $url = $service->pay($order);
            } catch (\Throwable $th) {
                return $this->errorBadRequest($errMsg);
            }
            
            return $this->success([
                "pay_url" => $url
            ]);
        } elseif ($pay_type == "2") {
            // USDT
            $tx_image = $request->input("image", null);
            if (empty($tx_image)) {
                return $this->errorBadRequest("need upload tx image");
            }
            OrderUsdt::create([
                "user_id" => $user->id,
                "order_id" => $order->id,
                "amount" => $amount,
                "image" => $tx_image,
                "status" => 0
            ]);

            return $this->ok();
        } elseif ($pay_type == "3") {
            // YTPAY
            $service = new YTPay();
            try {
                $url = $service->pay($order);
            } catch (\Throwable $th) {
                return $this->errorBadRequest($errMsg);
            }
            
            return $this->success([
                "pay_url" => $url
            ]);
        } elseif ($pay_type == "4") {
            // GSPAY
            $service = new GSPay();
            try {
                $data = $service->pay($order);
            } catch (\Throwable $th) {
                return $this->errorBadRequest($errMsg);
            }
            
            return $this->success($data);
        } elseif ($pay_type == "5") {
            // WEPAY
            $service = new WEPay();
            try {
                $url = $service->pay($order);
            } catch (\Throwable $th) {
                return $this->errorBadRequest($errMsg);
            }
            
            return $this->success([
                "pay_url" => $url
            ]);
        } elseif ($pay_type == "6") {
            // DFPAY
            $service = new DFPay();
            try {
                $url = $service->pay($order);
            } catch (\Throwable $th) {
                return $this->errorBadRequest($errMsg);
            }
            
            return $this->success([
                "pay_url" => $url
            ]);
        } elseif ($pay_type == "7") {
            // SharkPay
            $service = new SharkPay();
            try {
                $url = $service->pay($order);
            } catch (\Throwable $th) {
                return $this->errorBadRequest($errMsg);
            }
            
            return $this->success([
                "pay_url" => $url
            ]);
        } elseif ($pay_type == "8") {
            // GTPay
            $service = new GTPay();
            try {
                $url = $service->pay($order);
            } catch (\Throwable $th) {
                return $this->errorBadRequest($errMsg);
            }
            
            return $this->success([
                "pay_url" => $url
            ]);
        } elseif ($pay_type == "9") {
            // PPay
            $service = new PPay();
            try {
                $url = $service->pay($order);
            } catch (\Throwable $th) {
                return $this->errorBadRequest($errMsg);
            }
            
            return $this->success([
                "pay_url" => $url
            ]);
        } elseif ($pay_type == "10") {
            // MPAY
            $service = new MPay();
            try {
                $url = $service->pay($order);
            } catch (\Throwable $th) {
                report($th);
                return $this->errorBadRequest($th->getMessage());
            }
            
            return $this->success([
                "pay_url" => $url
            ]);
        } elseif ($pay_type == "11") {
            // FFPAY
            $service = new FFPay();
            try {
                $url = $service->pay($order);
            } catch (\Throwable $th) {
                report($th);
                return $this->errorBadRequest($th->getMessage());
            }
            
            return $this->success([
                "pay_url" => $url
            ]);
        } elseif ($pay_type == "12") {
            // XDPay-x1
            $service = new XDPay("x1");
            try {
                $url = $service->pay($order);
            } catch (\Throwable $th) {
                return $this->errorBadRequest($th->getMessage());
            }
            
            return $this->success([
                "pay_url" => $url
            ]);
        } elseif ($pay_type == "13") {
            // XDPay-dgm
            $service = new XDPay("dgm");
            try {
                $url = $service->pay($order);
            } catch (\Throwable $th) {
                return $this->errorBadRequest($th->getMessage());
            }
            
            return $this->success([
                "pay_url" => $url
            ]);
        } elseif ($pay_type == "14") {
            // XDPay
            $service = new XDPay("x2");
            try {
                $url = $service->pay($order);
            } catch (\Throwable $th) {
                return $this->errorBadRequest($th->getMessage());
            }
            
            return $this->success([
                "pay_url" => $url
            ]);
        } elseif ($pay_type == "15") {
            // WOWPay
            $service = new WOWPay();
            try {
                $url = $service->pay($order);
            } catch (\Throwable $th) {
                return $this->errorBadRequest($th->getMessage());
            }
            
            return $this->success([
                "pay_url" => $url
            ]);
        } elseif ($pay_type == "16") {
            // PTMPay
            $service = new PTMPay();
            try {
                $url = $service->pay($order);
            } catch (\Throwable $th) {
                return $this->errorBadRequest($th->getMessage());
            }
            
            return $this->success([
                "pay_url" => $url
            ]);
        } else {
            return $this->errorForbidden("Not Support");
        }
    }

    // USDT支付渠道
    public function getUsdtChannel(Request $request){
        $address = setting("TRC_USDT_RECEIVE_ADDRESS", null);
        return $this->success([
            [
                "channel" => "TRC-20", "address" => $address
            ],
        ]);
    }

    // 创建提现订单
    public function postWithdrawal(Request $request) {
        $pay_type = $request->input("pay_type", "0");
        $amount = $request->input("amount", "10");
        $bankcard_id = $request->input("bankcard_id", "0");

        $user = auth()->user();

        // 检查交易密码
        // 2020804: gold 提现需要支付密码那个校验也注释一下。
        // try {
        //     $user->checkTradePassword();
        // } catch (\Throwable $th) {
        //     return $this->errorBadRequest($th->getMessage());
        // }

        // 统计已提现次数
        $withdrawal_count = UserWithdrawal::where("user_id", $user->id)
            ->where("pay_status", 1)
            ->count();

        // 最小金额检查
        if ($withdrawal_count == 0) {
            // 第一次提现
            $min_amount = intval(
                setting("WITHDRAWAL_FIRST_MIN_AMOUNT", 500)
            );
        } else if ($withdrawal_count == 1) {
            // 第二次提现
            $min_amount = intval(
                setting("WITHDRAWAL_SECOND_MIN_AMOUNT", 500)
            );
        } else {
            // 第三次及以后
            $min_amount = intval(
                setting("WITHDRAWAL_MIN_AMOUNT", 500)
            );
        }
        
        if (floatval($amount) < $min_amount) {
            return $this->errorBadRequest("Has successfully withdrawn ".$withdrawal_count." times"."the min amount is " . $min_amount);
        }

        // 提现金额仅能是最小金额的整数倍
        // 20230803: 删除
        // if (fmod($amount, $min_amount) > 0) {
        //     return $this->errorBadRequest("please submit an integer amount");
        // }

        // 检查今天是否有提现
        // $today_exists = UserWithdrawal::where("user_id", $user->id)
        //     ->whereDate("created_at", today())
        //     ->exists();
        // if ($today_exists) {
        //     return $this->errorBadRequest("can only withdraw once a day");
        // }

        // 检查银行卡信息
        $bankcard = UserBankcard::where("id", $bankcard_id)
            ->first();
        if (empty($bankcard)) {
            return $this->errorBadRequest("bankcard info not exists");
        }

        
        // 检查余额是否足够
        if ($user->available_balance < $amount) {
            return $this->errorBadRequest("Insufficient available balance");
        }
            
        //查询订单是否已经全部完成
        
        // $TaskOrder = TaskOrder::where("user_id",$user->id)->where("task_id",$user->task_id)->orderBy("created_at","desc")->first();
        // if(empty($TaskOrder)){
        //     return $this->errorBadRequest("You can withdraw cash only after completing all tasks"); 
        // }
        // if($TaskOrder->status!=2){
        //      return $this->errorBadRequest("You can withdraw cash only after completing all tasks"); 
        // }
        // $TaskModelCount = TaskModel::where("task_index_id",$user->task_id)->count();
        // if($TaskOrder->model_index!=$TaskModelCount){
        //     return $this->errorBadRequest("You can withdraw cash only after completing all tasks"); 
        // }
        // 用户提现增加条件
        // 20230803：用户提现必须购买过商品才可以提现。
        // $is_buy = UserItem::where("user_id", $user->id)->exists();
        // if (!$is_buy) {
        //     return $this->errorBadRequest("You have not purchased any products");
        // }

        // 扣除手续费
        // 20230807: 已调整，代码同步
        // if ($withdrawal_count > 1) {
            // 第三次及以后
            if ($pay_type == 2) {
                $service_fee = floatval(
                    setting("WITHDRAWAL_SERVICE_FEE_USDT", 5)
                );
            } else {
                $service_fee = floatval(
                    setting("WITHDRAWAL_SERVICE_FEE", 5)
                );
            }
            
        // } else {
        //     // 头两次免费
        //     $service_fee = 0;
        // }
        
        $withdraw_amount = $amount;
        if ($service_fee > 0) {
            $withdraw_amount = $withdraw_amount * (1 - ($service_fee / 100));
        }

        $user_id = $user->id;
        $outTradeNo = PaymentTool::generateOutTradeNo();
        DB::beginTransaction();
        try {
            // 创建提现订单
            UserWithdrawal::create([
                "user_id" => $user_id,
                "status" => 0,
                "bankcard_id" => $bankcard_id,
                "amount" => $withdraw_amount,
                "withdrawal_no" => $outTradeNo,
                "pay_type" => $pay_type,
                "pay_status" => 2,
                "order_status" => 2,
                "after_balance" => $user->balance - $amount
            ]);

            // 减少用户余额
            $userRepository = app(UserRepository::class);
            $userRepository->addBalance([
                "user_id" => $user_id,
                "money" => 0 - $amount,
                "log_type" => 2
            ]);
            
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->errorBadRequest($th->getMessage());
        }

        return $this->ok();
    }

    // 将余额转换为任务金
    public function postConvertToMission(Request $request) {
        $amount = $request->input("amount", 0);

        $user = auth()->user();
        // 检查余额是否足够
        if ($user->balance < $amount) {
            return $this->errorBadRequest("Insufficient balance");
        }

        
        $userRepository = app(UserRepository::class);

        // 减少用户余额
        $userRepository->addBalance([
            "user_id" => $user->id,
            "money" => 0 - $amount,
            "log_type" => 16,
            "balance_type" => "balance"
        ]);
        // 增加用户任务金
        $userRepository->addBalance([
            "user_id" => $user->id,
            "money" => $amount,
            "log_type" => 16,
            "balance_type" => "mission_balance"
        ]);

        return $this->ok();
    }

    // 最新的提现记录
    public function getNewestWithdrawal(Request $request) {
        $paginator = UserWithdrawal::orderBy("created_at", "desc")
            ->with(["user:id,name,mobile"])
            ->paginate(20);
        $paginator->getCollection()->transform(function ($value) {
            // example: 8768479588 -> 876*****88
            $value->user->mobile = Str::mask($value->user->mobile, "*", 3, 5);
            return $value;
        });

        return $this->success($paginator);
    }
}
