<?php

namespace App\Services\Payment;

use App\Exceptions\NotImplementedException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// GSPay服务类
class GSPay
{

    protected $config;

    public function __construct($config_name = "payment.gspay")
    {
        $this->config = config($config_name);
    }

    // 代收(支付)
    // (银行)代(替商户)收(款)
    public function pay($order) {
        $url = "http://pay.g8apay.com/api/pay/create_order";
        $data = [
            "mchId" => $this->config["mid"],
            "appId" => $this->config["appid"],
            "productId" => 8021,
            "mchOrderNo" => $order->order_no,
            "currency" => "INR",
            "amount" => floatval($order->price) * 100,
            "returnUrl" => url("api/payment/notify/pay/gspay", [], true),
            "notifyUrl" => url("api/payment/notify/pay/gspay", [], true),
            "subject" => "all",
            "body" => "email:520155@gmail.com/name:tom/phone:7894561230",
            "extra" => '{"openId":"o2RvowBf7sOVJf8kJksUEMceaDqo"}',
        ];

        // 生成签名
        $sign = $this->build_sign($data);
        $data["sign"] = $sign;

        $response = Http::asForm()
            ->post($url, $data);
        $result = $response->json();
        
        if($result["retCode"] == "SUCCESS"){
            // $order->trade_no = $data["payOrderId"];
            return $result["payParams"];
        }else{
            Log::info("GSPAY_PAY_ERROR:" . $result["retMsg"]);
            throw new \Exception("pay error");    
        }

    }

    // 代收回调通知验证
    public function pay_notify_verify() {
        $returnArray = array( // 返回字段
            "income" => request("income"), // 商户ID
            "payOrderId" =>  request("payOrderId"), // 订单号
            "mchId" =>  request("mchId"), // 交易金额
            "appId" =>  request("appId"), // 交易时间
            "productId" =>  request("productId"), // 支付流水号
            "mchOrderNo" => request("mchOrderNo"),
            "amount" => request("amount"),
            "status" => request("status"),
            "paySuccTime" => request("paySuccTime"),
            "backType" => request("backType")
        );
        Log::debug("GSPAY_PAY_NOTIFY_REQUEST:", request()->all());

        $sign = $this->build_sign($returnArray);

        if ($sign == request("sign")) {
            $status = request("status");
            if ($status == "2") {
                    $order_no = request("mchOrderNo");
                    $msg = "GSPAY_NOTIFY_SUCCESS:订单号: " . $order_no;
                    Log::info($msg);
                    return $order_no;
            } else {
                Log::debug("GSPAY_PAY_NOTIFY_ERROR:RETURN CODE is " . $status);
                throw new \Exception("return code is " . $status);    
            }
        } else {
            Log::debug("GSPAY_PAY_NOTIFY_ERROR:SIGN ERROR");
            throw new \Exception("sign error");
            
        }
    }

    // 代付(提现/转账)
    // (银行)代(替商户)付(款)
    public function transfer($withdrawl) {
        if (empty($withdrawl->bankcard)){
            throw new \Exception("bank card info is error");  
        }
        $url = "http://pay.g8apay.com/api/agentpay/apply";
        $data = [
            "mchId" => $this->config["mid"],
            "mchOrderNo" => $withdrawl->withdrawal_no,
            "amount" => floatval($withdrawl->amount) * 100,
            "accountName" => $withdrawl->bankcard->name,
            "accountNo" => $withdrawl->bankcard->card_no,
            "bankNumber" => $withdrawl->bankcard->ifsc_code,
            "notifyUrl" => url("api/payment/notify/transfer/gspay", [], true),
            "remark" => "email:520155@gmail.com/phone:9784561230/address:1, Vishwanath Rao Road, Madhava Nagar/mode:bank",
            "reqTime" => date("YmdHis"),
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
            Log::info("GSPAY_TRANSFER_ERROR:" . $raw);
            throw new \Exception("transfer error");
        }
        
        return $result;
    }

    // 代付回调通知验证
    public function transfer_notify_verify() {
        $returnArray = array( // 返回字段
            "mchOrderNo" =>  request("mchOrderNo"), // 订单号
            "agentpayOrderId" =>  request("agentpayOrderId"), // 支付流水号
            "status" => request("status"), // 状态
            "fee" =>  request("fee"), // 手续费
        );
        Log::debug("GSPAY_TRNASFER_NOTIFY_REQUEST:", $returnArray);

        $sign = $this->build_sign($returnArray);

        if ($sign == request("sign")) {
            $status = request("status");
            if ($status == "2") {
                    $order_no = request("mchOrderNo");
                    $msg = "GSPAY_TRNASFER_SUCCESS:订单号: " . $order_no;
                    Log::info($msg);
                    return $order_no;
            } else {
                Log::debug("GSPAY_TRNASFER_NOTIFY_ERROR:RETURN CODE is " . $status);
                throw new \Exception("return code is " . $status);    
            }
        } else {
            Log::debug("GSPAY_TRNASFER_NOTIFY_ERROR:SIGN ERROR");
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
        return strtoupper(md5($md5str . "key=" . $this->config["key"]));
    }


    
}