<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// PPay服务类
class PPay
{

    protected $config;

    public function __construct()
    {
        $this->config = config("payment.ppay");
    }

    // 代收(支付)
    // (银行)代(替商户)收(款)
    public function pay($order) {
        $url = "https://ord.ppayglobal.com/pay/order";
        $data = [
            "merNo" => $this->config["mid"],
            "merchantOrderNo" => $order->order_no,
            "payCode" => "121",
            "amount" => $order->price,
            "notifyUrl" => url("api/payment/notify/pay/ppay", [], true),
            "currency" => "INR",
            "goodsName" => "Recharge",
            // "callbakUrl" => url("api/payment/notify/pay/ppay", [], true),
            
        ];

        // 生成签名
        $sign = $this->build_sign($data);
        $data["sign"] = $sign;
        
        $response = Http::asForm()
            ->post($url, $data);
        $result = $response->json();

        
        if($result["code"] == "SUCCESS"){
            return $result["payLink"];
        }else{
            Log::info("PPAY_PAY_ERROR:" . $result["msg"]);
            throw new \Exception("pay error");    
        }

    }

    // 代收回调通知验证
    public function pay_notify_verify() {

        $returnArray = array( // 返回字段
            "mchNo" => request("mchNo"), // 商户号
            "merchantOrderNo" =>  request("merchantOrderNo"), // 商户订单号
            "ptOrderNo" =>  request("ptOrderNo"), // 平台订单号
            "amount" =>  request("amount"), // 代付金额，支持2位小数
            "result" =>  request("result"), // 订单状态：1：支付成功
        );
        Log::debug("PPAY_PAY_NOTIFY_REQUEST:", $returnArray);

        $sign = $this->build_sign($returnArray);

        if ($sign == request("sign")) {
            $status = request("result");
            if ($status == "1") {
                    $order_no = request("merchantOrderNo");
                    $msg = "PPAY_NOTIFY_SUCCESS:订单号: " . $order_no;
                    Log::info($msg);
                    return $order_no;
            } else {
                Log::debug("PPAY_PAY_NOTIFY_ERROR:RETURN CODE is " . $status);
                throw new \Exception("return code is " . $status);    
            }
        } else {
            Log::debug("PPAY_PAY_NOTIFY_ERROR:SIGN ERROR");
            throw new \Exception("sign error");
            
        }
    }

    // 代付(提现/转账)
    // (银行)代(替商户)付(款)
    public function transfer($withdrawl) {
        if (empty($withdrawl->bankcard)){
            throw new \Exception("bank card info is error");  
        }
        $url = "https://withdraw.ppayglobal.com/withdraw/createOrder";
        $data = [
            "merNo" => $this->config["mid"],
            "merchantOrderNo" => $withdrawl->withdrawal_no,
            "currency" => "INR",
            "amount" => $withdrawl->amount,
            "bankCode" => "UPI",
            "customerName" => $withdrawl->bankcard->name,
            "customerAccount" => $withdrawl->bankcard->card_no,
            "customerMobile" => $withdrawl->bankcard->mobile,
            "customerEmail" => $withdrawl->bankcard->email,
            "accth" => "2",
            "notifyUrl" => url("api/payment/notify/transfer/ppay", [], true),
            // "pay_callbackurl" => url("api/payment/notify/transfer/ppay", [], true),
            
        ];
        // 生成签名
        $sign = $this->build_sign($data);
        $data["sign"] = $sign;
        
        $response = Http::asForm()
            ->post($url, $data);
        $result = $response->json();

        
        if($result["code"] != "SUCCESS"){
            Log::info("PPAY_TRANSFER_ERROR:" . $result["msg"]);
            throw new \Exception("transfer error");    
        }
        
        return $result;
    }

    // 代付回调通知验证
    public function transfer_notify_verify() {
        $returnArray = array( // 返回字段
            "code" => request("code"), // 固定:SUCCESS
            "result" =>  request("result"), // 订单状态：1：代付成功 2：代付失败
            "amount" =>  request("amount"), // 代付金额，支持2位小数
            "ptOrderNo" =>  request("ptOrderNo"), // 平台订单号
            "merNo" =>  request("merNo"), // 商户号
            "merchantOrderNo" => request("merchantOrderNo"), // 商户订单号
        );

        $failMsg = request("failMsg", null);
        if (!empty("failMsg")) {
            $returnArray["failMsg"] = $failMsg;
        }

        Log::debug("PPAY_TRNASFER_NOTIFY_REQUEST:", $returnArray);

        $sign = $this->build_sign($returnArray);

        if ($sign == request("sign")) {
            $status = request("result");
            if ($status == "1") {
                    $order_no = request("merchantOrderNo");
                    $msg = "PPAY_TRNASFER_SUCCESS:订单号: " . $order_no;
                    Log::info($msg);
                    return $order_no;
            } else {
                Log::debug("PPAY_TRNASFER_NOTIFY_ERROR:FAIL MESSAGE is " . $failMsg);
                throw new \Exception("fail message is " . $status);    
            }
        } else {
            Log::debug("PPAY_TRNASFER_NOTIFY_ERROR:SIGN ERROR");
            throw new \Exception("sign error");
            
        }
    }

    // 生成签名
    protected function build_sign($data) {
        ksort($data);
        reset($data);
        $md5str = "";
        foreach ($data as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        return strtolower(md5($md5str . "key=" . $this->config["apikey"]));
    }


    
}