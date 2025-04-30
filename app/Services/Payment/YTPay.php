<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// YTPay服务类
class YTPay
{

    protected $config;

    public function __construct()
    {
        $this->config = config("payment.ytpay");
    }

    // 代收(支付)
    // (银行)代(替商户)收(款)
    public function pay($order) {
        if (empty($order->user->bankcard)){
            throw new \Exception("bank card info is error");  
        }
        $url = "https://yt-pay.com/api/pay/merchantApi/pay";
        $data = [
            "merchantCode" => $this->config["mid"],
            "subOrderNo" => $order->order_no,
            "gatewayNo" => "116",
            "amount" => $order->price,
            "customerName" => $order->user->bankcard->name,
            "customerMobile" => $order->user->bankcard->mobile,
            "customerEmail" => $order->user->bankcard->email,
            "timeStamp" => now("Asia/Shanghai")->getTimestampMs()
            // "pay_notifyurl" => url("api/payment/notify/pay/cspay", [], true),
            // "pay_callbackurl" => url("api/payment/notify/pay/cspay", [], true),
            
        ];
        // dd(now("Asia/Shanghai")->timestamp);

        // 生成签名
        $sign = $this->build_sign($data);
        $response = Http::withHeaders([
            'X-Sign' => $sign
        ])->post($url, $data);
        // ->withOptions([
        //     'proxy' => "http://127.0.0.1:7890",
        // ])
        $data = $response->json();

        
        if($data["code"] == 0){
            return $data["paymentUrl"];
        }else{
            Log::info("YTPAY_PAY_ERROR:" . $data["message"]);
            throw new \Exception("pay error");    
        }

    }

    // 代收回调通知验证
    public function pay_notify_verify() {

        $returnArray = array( // 返回字段
            "orderNo" => request("orderNo"), // 订单号
            "subOrderNo" =>  request("subOrderNo"), // 对接商户订单号
            "merchantCode" =>  request("merchantCode"), // 商户号
            "amount" =>  request("amount"), // 订单金额 (保留两位小数)
            "fee" =>  request("fee"), // 手续费（仅支付成功返回 ）
            "receiveAmount" => request("receiveAmount"), // 实际到账金额
            "timeStamp" => request("timeStamp"), // 时间戳
            "status" => request("status"), // 订单状态
        );
        Log::debug("YTPAY_PAY_NOTIFY_REQUEST:", $returnArray);

        if (request()->ip() != "23.224.111.214") {
            Log::debug("YTPAY_PAY_NOTIFY_ERROR:SIGN ERROR:ERROR IP");
            throw new \Exception("sign error");
        }
        return $returnArray["subOrderNo"];
    }

    // 代付(提现/转账)
    // (银行)代(替商户)付(款)
    public function transfer($withdrawl) {
        if (empty($withdrawl->bankcard)){
            throw new \Exception("bank card info is error");  
        }
        $url = "https://yt-pay.com/api/pay/merchantApi/transfer";
        $data = [
            "merchantCode" => $this->config["mid"],
            "subOrderNo" => $withdrawl->withdrawal_no,
            "amount" => $withdrawl->amount,
            "customerName" => $withdrawl->bankcard->name,
            "customerMobile" => $withdrawl->bankcard->mobile,
            "customerEmail" => $withdrawl->bankcard->email,
            "ifsc" => $withdrawl->bankcard->ifsc_code,
            "customerAccount" => $withdrawl->bankcard->card_no,
            "timeStamp" => now("Asia/Shanghai")->getTimestampMs()
            // "pay_notifyurl" => url("api/payment/notify/pay/cspay", [], true),
            // "pay_callbackurl" => url("api/payment/notify/pay/cspay", [], true),
            
        ];
        // 生成签名
        $sign = $this->build_sign($data);
        $response = Http::withHeaders([
            'X-Sign' => $sign
        ])->post($url, $data);
        // ->withOptions([
        //     'proxy' => "http://127.0.0.1:7890",
        // ])
        $data = $response->json();

        
        if($data["code"] != 0){
            Log::info("YTPAY_TRANSFER_ERROR:" . $data["message"]);
            throw new \Exception("transfer error");    
        }
        
        return $data;
    }

    // 代付回调通知验证
    public function transfer_notify_verify() {
        $returnArray = array( // 返回字段
            "orderNo" => request("orderNo"), // 订单号
            "subOrderNo" =>  request("subOrderNo"), // 对接商户订单号
            "merchantCode" =>  request("merchantCode"), // 商户号
            "amount" =>  request("amount"), // 订单金额 (保留两位小数)
            "fee" =>  request("fee"), // 手续费（仅支付成功返回 ）
            "timeStamp" => request("timeStamp"), // 时间戳
            "status" => request("status"), // 订单状态
        );
        Log::debug("YTPAY_TRNASFER_NOTIFY_REQUEST:", $returnArray);

        if (request()->ip() != "23.224.111.214") {
            Log::debug("YTPAY_TRNASFER_NOTIFY_ERROR:SIGN ERROR:ERROR IP");
            throw new \Exception("sign error");
        }
        return $returnArray["subOrderNo"];
    }

    // 生成签名
    protected function build_sign($data) {
        ksort($data);
        reset($data);
        $md5str = "";
        foreach ($data as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        return strtolower(md5($md5str . "secretKey=" . $this->config["apikey"]));
    }


    
}