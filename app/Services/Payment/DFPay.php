<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// DFPay服务类
class DFPay
{

    protected $config;

    public function __construct()
    {
        $this->config = config("payment.dfpay");
    }

    // 代收(支付)
    // (银行)代(替商户)收(款)
    public function pay($order) {
        $url = "https://apis.dfpay.in/client/collect/create";
        $data = [
            "merchant" => $this->config["mid"],
            "payCode" => $this->config["pay_code"],
            "amount" => $order->price,
            "orderId" => $order->order_no,
            "notifyUrl" => url("api/payment/notify/pay/dfpay", [], true),
            "callbackUrl" => url("api/payment/notify/pay/dfpay", [], true),
        ];

        // 生成签名
        $sign = $this->build_sign($data);
        $data["sign"] = $sign;

        $response = Http::asForm()
            ->post($url, $data);
        $raw = $response->getBody()->getContents();
        try {
            $result = json_decode($raw, true);
        } catch (\Throwable $th) {
            Log::info("DFPAY_PAY_ERROR:" . $raw);
            throw new \Exception("pay error");
        }

        if($result["code"] == "200"){
            return $result["data"]["url"];
        }else{
            Log::info("DFPAY_PAY_ERROR:" . $result["msg"]);
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
        Log::debug("DFPAY_PAY_NOTIFY_REQUEST:", $returnArray);

        $sign = $this->build_sign($returnArray);

        if ($sign == request("sign")) {
            $returncode = request("status");
            if ($returncode == "1") {
                    $order_no = request("orderId");
                    $msg = "DFPAY_NOTIFY_SUCCESS:订单号: " . $order_no;
                    Log::info($msg);
                    return $order_no;
            } else {
                Log::debug("DFPAY_PAY_NOTIFY_ERROR:RETURN CODE is " . $returncode);
                throw new \Exception("return code is " . $returncode);    
            }
        } else {
            Log::debug("DFPAY_PAY_NOTIFY_ERROR:SIGN ERROR");
            throw new \Exception("sign error");
            
        }
    }

    // 代付(提现/转账)
    // (银行)代(替商户)付(款)
    public function transfer($withdrawl) {
        if (empty($withdrawl->bankcard)){
            throw new \Exception("bank card info is error");  
        }
        $url = "https://apis.dfpay.in/client/pay/create";
        $data = [
            "merchant" => $this->config["mid"],
            "payCode" => $this->config["pay_code"],
            "amount" => $withdrawl->amount,
            "orderId" => $withdrawl->withdrawal_no,
            "notifyUrl" => url("api/payment/notify/transfer/dfpay", [], true),
            "bankAccount" => $withdrawl->bankcard->card_no,
            "customName" => $withdrawl->bankcard->name,
            "remark" => $withdrawl->bankcard->ifsc_code,
        ];
        // 生成签名
        $sign = $this->build_sign($data);
        $data["sign"] = $sign;

        $response = Http::asForm()
            ->post($url, $data);
        $raw = $response->getBody()->getContents();
        try {
            $result = json_decode($raw, true);
        } catch (\Throwable $th) {
            Log::info("DFPAY_TRANSFER_ERROR:" . $raw);
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
        Log::debug("DFPAY_TRNASFER_NOTIFY_REQUEST:", $returnArray);

        $sign = $this->build_sign($returnArray);

        if ($sign == request("sign")) {
            $returncode = request("status");
            if ($returncode == "1") {
                    $order_no = request("orderId");
                    $msg = "DFPAY_TRNASFER_SUCCESS:订单号: " . $order_no;
                    Log::info($msg);
                    return $order_no;
            } else {
                Log::debug("DFPAY_TRNASFER_NOTIFY_ERROR:RETURN CODE is " . $returncode);
                throw new \Exception("return code is " . $returncode);    
            }
        } else {
            Log::debug("DFPAY_TRNASFER_NOTIFY_ERROR:SIGN ERROR");
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