<?php

namespace App\Services\Payment;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// MPay服务类
class MPay
{

    protected $config;

    public function __construct()
    {
        $this->config = config("payment.mpay");
    }

    // 代收(支付)
    // (银行)代(替商户)收(款)
    public function pay($order) {
        $url = "https://api.mpayin.com/admin/platform/api/out/pay";
        $data = [
            "merchantNo" => $this->config["mid"],
            "orderNo" => $order->order_no,
            "amount" => sprintf("%.2f", $order->price),
            "type" => 8, // UPI
            "notifyUrl" => url("api/payment/notify/pay/mpay", [], true),
            "userName" => $order->user->name,
            "ext" => "ext",
            "version" => "2.0.2",
        ];

        // 生成签名
        $sign = $this->build_sign($data);
        $data["sign"] = $sign;

        $response = Http::post($url, $data);
        $resp_data = $response->json();

        // 判断CODE是否等于0
        if ($resp_data["code"] != 0) {
            Log::info("MPAY_PAY_ERROR:" . $resp_data["message"]);
            throw new \Exception("pay response error");
        }
    
        $resp_sign = Arr::pull($resp_data, "sign", "");
        // 响应验签
        if ($this->build_sign($resp_data) != $resp_sign ) {
            Log::info("MPAY_PAY_RESPONSE", $response->json());
            throw new \Exception("pay response sign error");
        }

        return $resp_data["url"];

    }

    // 代收回调通知验证
    public function pay_notify_verify() {
        $returnArray = array( // 返回字段
            "merchantNo" => request("merchantNo"), // 商户号
            "orderNo" =>  request("orderNo"), // 商户订单号
            "amount" =>  request("amount"), // 交易金额
            "realAmount" =>  request("realAmount"), // 实际金额
            "ext" =>  request("ext"), // 透传参数
            "status" =>  request("status"), // 订单状态
            "msg" =>  request("msg"), // 状态解释
            "version" => request("version"), // 版本号
            "startTime" => request("startTime"), // 订单开始时间
            "finishPayTime" => request("finishPayTime"), // 订单完成支付的时间
            "platformOrderNo" => request("platformOrderNo"), // 平台订单号
        );

        $replacementOrderNo = request("replacementOrderNo", null);
        if (!empty($replacementOrderNo)) {
            // 补单商户号
            $returnArray["replacementOrderNo"] = $replacementOrderNo;
        }


        Log::debug("MPAY_PAY_NOTIFY_REQUEST:", $returnArray);

        $sign = $this->build_sign($returnArray);

        if ($sign == request("sign")) {
            $returncode = request("status");
            if ($returncode == "1") {
                    $order_no = request("orderNo");
                    $msg = "MPAY_PAY_NOTIFY_SUCCESS:订单号: " . $order_no;
                    Log::info($msg);
                    return $order_no;
            } else {
                Log::debug("MPAY_PAY_NOTIFY_ERROR:RETURN CODE is " . $returncode);
                throw new \Exception("return code is " . $returncode);    
            }
        } else {
            Log::debug("MPAY_PAY_NOTIFY_ERROR:SIGN ERROR");
            throw new \Exception("sign error");
            
        }
    }

    // 代付(提现/转账)
    // (银行)代(替商户)付(款)
    public function transfer($withdrawl) {
        if (empty($withdrawl->bankcard)){
            throw new \Exception("bank card info is error");  
        }
        $url = "https://api.mpayin.com/admin/platform/api/out/df";
        $data = [
            "merchantNo" => $this->config["mid"],
            "orderNo" => $withdrawl->withdrawal_no,
            "amount" => sprintf("%.2f", $withdrawl->amount),
            "type" => 1, // UPI
            "notifyUrl" => url("api/payment/notify/transfer/mpay", [], true),
            
            "ext" => "ext",
            "version" => "2.0.2",
            "name" => $withdrawl->bankcard->name,
            "account" => $withdrawl->bankcard->card_no,
            "ifscCode" => $withdrawl->bankcard->ifsc_code,
        ];
        // 生成签名
        $sign = $this->build_sign($data);
        $data["sign"] = $sign;

        $response = Http::post($url, $data);
        $resp_data = $response->json();

        // 判断CODE是否等于0
        if ($resp_data["code"] != 0) {
            Log::info("MPAY_TRANSFER_ERROR:" . $resp_data["message"]);
        }
    
        $resp_sign = Arr::pull($resp_data, "sign", "");
        // 响应验签
        if ($this->build_sign($resp_data) != $resp_sign ) {
            Log::info("MPAY_TRANSFER_RESPONSE", $response->json());
            throw new \Exception("transfer response sign error");
        }

        return $resp_data;
    }

    // 代付回调通知验证
    public function transfer_notify_verify() {
        $returnArray = array( // 返回字段
            "merchantNo" => request("merchantNo"), // 商户号
            "orderNo" =>  request("orderNo"), // 商户订单号
            "amount" =>  request("amount"), // 交易金额
            "realAmount" =>  request("realAmount"), // 实际金额
            "ext" =>  request("ext"), // 透传参数
            "status" =>  request("status"), // 订单状态
            "msg" =>  request("msg"), // 状态解释
            "version" => request("version"), // 版本号
            "startTime" => request("startTime"), // 订单开始时间
            "finishPayTime" => request("finishPayTime"), // 订单完成支付的时间
            "platformOrderNo" => request("platformOrderNo"), // 平台订单号
        );

        $replacementOrderNo = request("replacementOrderNo", null);
        if (!empty($replacementOrderNo)) {
            // 补单商户号
            $returnArray["replacementOrderNo"] = $replacementOrderNo;
        }


        Log::debug("MPAY_TRANSFER_NOTIFY_REQUEST:", $returnArray);

        $sign = $this->build_sign($returnArray);

        if ($sign == request("sign")) {
            $returncode = request("status");
            if ($returncode == "1") {
                    $order_no = request("orderNo");
                    $msg = "MPAY_TRANSFER_NOTIFY_SUCCESS:订单号: " . $order_no;
                    Log::info($msg);
                    return $order_no;
            } else {
                Log::debug("MPAY_TRANSFER_NOTIFY_ERROR:RETURN CODE is " . $returncode);
                throw new \Exception("return code is " . $returncode);    
            }
        } else {
            Log::debug("MPAY_TRANSFER_NOTIFY_ERROR:SIGN ERROR");
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
        return strtoupper(md5($md5str . "key=" . $this->config["apikey"]));
    }


    
}