<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// XDPay服务类
class XDPay
{
    protected $merchant_key;
    protected $config;

    public function __construct($merchant="x1")
    {
        $this->merchant_key = $merchant;
        $this->config = config("payment.xdpay-" . $this->merchant_key);
    }

    // 代收(支付)
    // (银行)代(替商户)收(款)
    public function pay($order) {
        $url = "https://apis.xdpay168.com/client/collect/create";
        $data = [
            "merchant" => $this->config["mid"],
            "payCode" => $this->config["pay_pay_code"],
            "amount" => $order->price,
            "orderId" => $order->order_no,
            "notifyUrl" => url("api/payment/notify/pay/xdpay/" . $this->merchant_key, [], true),
            "callbackUrl" => url("api/payment/notify/pay/xdpay/" . $this->merchant_key, [], true),
        ];

        // 生成签名
        $sign = $this->build_sign($data);
        $data["sign"] = $sign;

        $response = Http::post($url, $data);
        $raw = $response->getBody()->getContents();
        try {
            $result = json_decode($raw, true);
        } catch (\Throwable $th) {
            Log::info("XDPAY_PAY_ERROR:" . $raw);
            throw new \Exception("pay error");
        }

        if($result["code"] == "200"){
            return $result["data"]["url"];
        }else{
            Log::info("XDPAY_PAY_ERROR:" . $result["msg"]);
            throw new \Exception("pay error");    
        }

    }

    // 代收回调通知验证
    public function pay_notify_verify() {
        $returnArray = array( // 返回字段
            "platOrderId" => request("platOrderId"), // 平台订单号
            "orderId" =>  request("orderId"), // 商户订单号
            "amount" =>  request("amount"), // 实际支付金额
            "status" =>  request("status"), // 交易状态
        );
        Log::debug("XDPAY_PAY_NOTIFY_REQUEST:", $returnArray);

        $sign = $this->build_sign($returnArray);

        if ($sign == request("sign")) {
            $returncode = request("status");
            if ($returncode == "1") {
                    $order_no = request("orderId");
                    $msg = "XDPAY_NOTIFY_SUCCESS:订单号: " . $order_no;
                    Log::info($msg);
                    return $order_no;
            } else {
                Log::debug("XDPAY_PAY_NOTIFY_ERROR:RETURN CODE is " . $returncode);
                throw new \Exception("return code is " . $returncode);    
            }
        } else {
            Log::debug("XDPAY_PAY_NOTIFY_ERROR:SIGN ERROR");
            throw new \Exception("sign error");
            
        }
    }

    // 代付(提现/转账)
    // (银行)代(替商户)付(款)
    public function transfer($withdrawl) {
        if (empty($withdrawl->bankcard)){
            throw new \Exception("bank card info is error");  
        }
        $url = "https://apis.xdpay168.com/client/pay/create";
        $data = [
            "merchant" => $this->config["mid"],
            "payCode" => $this->config["transfer_pay_code"],
            "amount" => $withdrawl->amount,
            "orderId" => $withdrawl->withdrawal_no,
            "notifyUrl" => url("api/payment/notify/transfer/xdpay/" . $this->merchant_key, [], true),
            "bankAccount" => $withdrawl->bankcard->card_no,
            "customName" => $withdrawl->bankcard->name,
            "remark" => $withdrawl->bankcard->ifsc_code,
            "number" => $withdrawl->bankcard->mobile ?? $withdrawl->user->mobile
        ];
        // 生成签名
        $sign = $this->build_sign($data);
        $data["sign"] = $sign;

        $response = Http::post($url, $data);
        $raw = $response->getBody()->getContents();
        try {
            $result = json_decode($raw, true);
        } catch (\Throwable $th) {
            Log::info("XDPAY_TRANSFER_ERROR:" . $raw);
            throw new \Exception("transfer error");
        }

        return $result;
    }

    // 代付回调通知验证
    public function transfer_notify_verify() {
        $returnArray = array( // 返回字段
            "platOrderId" => request("platOrderId"), // 平台订单号
            "orderId" =>  request("orderId"), // 商户订单号
            "amount" =>  request("amount"), // 实际支付金额
            "status" =>  request("status"), // 交易状态
        );
        Log::debug("XDPAY_TRNASFER_NOTIFY_REQUEST:", $returnArray);

        $sign = $this->build_sign($returnArray);

        if ($sign == request("sign")) {
            $returncode = request("status");
            if ($returncode == "1") {
                    $order_no = request("orderId");
                    $msg = "XDPAY_TRNASFER_SUCCESS:订单号: " . $order_no;
                    Log::info($msg);
                    return $order_no;
            } else {
                Log::debug("XDPAY_TRNASFER_NOTIFY_ERROR:RETURN CODE is " . $returncode);
                throw new \Exception("return code is " . $returncode);    
            }
        } else {
            Log::debug("XDPAY_TRNASFER_NOTIFY_ERROR:SIGN ERROR");
            throw new \Exception("sign error");
            
        }
    }

    // 生成签名
    protected function build_sign($data, $keyType="key") {
        ksort($data);
        reset($data);
        $md5str = "";
        foreach ($data as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        return strtolower(md5($md5str . "key=" . $this->config[$keyType]));
    }
}