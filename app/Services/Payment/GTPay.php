<?php

namespace App\Services\Payment;

use App\Exceptions\NotImplementedException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// GTPay服务类
class GTPay
{
    protected $config;

    public function __construct($config_name = "payment.gtpay")
    {
        $this->config = config($config_name);
    }

    // 支付通道是什么：代收216，代付118

    // 代收(支付)
    // (银行)代(替商户)收(款)
    public function pay($order) {
        $url = "http://api.gtpay66.com/Index/index";
        $data = [
            "store_id" => $this->config["mid"], // 商户号 
            "out_trade_no" => $order->order_no, // 订单号
            "money" => sprintf("%.2f", $order->price), // 金额(单位：元 两位小数)
            "pay_type" => "216",
            "notify_url" => url("api/payment/notify/pay/gtpay", [], true),
            "return_url" => "https://abntop.com",
            "store_remark" => "充值",
            "time" => time(),
            "client_ip" => request()->ip()
        ];

        // 生成签名
        $sign = $this->build_sign($data);
        $data["sign"] = $sign;

        $response = Http::asForm()
            ->post($url, $data);
        $result = $response->json();

        if($result["code"] == "0"){
            return $result["data"]["pay_url"];
        }else{
            Log::info("GTPAY_PAY_ERROR:" . $result["msg"]);
            throw new \Exception("pay error");    
        }

    }

    // 代收回调通知验证
    public function pay_notify_verify() {
        $returnArray = array( // 返回字段
            "out_trade_no" => request("out_trade_no"), // 商户订单号
            "order_no" =>  request("order_no"), // 系统平台订单号
            "status" =>  request("status"), // 订单状态
            "money" =>  request("money"), // 订单金额
            "real_money" =>  request("real_money"), // 支付金额
            "remark" =>  request("remark"), // 系统备注
            "notify_time" => request("notify_time"), // 回调时间
            "is_supp" => request("is_supp"), // 是否补单
        );
        Log::debug("GTPAY_PAY_NOTIFY_REQUEST:", request()->all());

        $sign = $this->build_sign($returnArray);

        if ($sign == request("sign")) {
            $status = request("status");
            if ($status == "1") {
                    $order_no = request("out_trade_no");
                    $msg = "GTPAY_NOTIFY_SUCCESS:订单号: " . $order_no;
                    Log::info($msg);
                    return $order_no;
            } else {
                Log::debug("GTPAY_PAY_NOTIFY_ERROR:RETURN CODE is " . $status);
                throw new \Exception("return code is " . $status);    
            }
        } else {
            Log::debug("GTPAY_PAY_NOTIFY_ERROR:SIGN ERROR");
            throw new \Exception("sign error");
            
        }
    }

    // 代付(提现/转账)
    // (银行)代(替商户)付(款)
    public function transfer($withdrawl) {
        if (empty($withdrawl->bankcard)){
            throw new \Exception("bank card info is error");  
        }
        $url = "http://api.gtpay66.com/Index/df_order";
        $data = [
            "store_id" => $this->config["mid"], // 商户号 
            "out_trade_no" => $withdrawl->withdrawal_no, // 订单号
            "money" => sprintf("%.2f", $withdrawl->amount), // 金额(单位：元 两位小数)
            "pay_type" => "118", // 代付通道
            "collect_name" => $withdrawl->bankcard->bank_name,
            "collect_account" => $withdrawl->bankcard->card_no,
            "full_name" => $withdrawl->bankcard->name,
            "card_no" => $withdrawl->bankcard->subbranch,
            "notify_url" => url("api/payment/notify/transfer/gtpay", [], true),
            "store_remark" => "提现",
            "time" => time(),
            "client_ip" => request()->ip()
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
            Log::info("GTPAY_TRANSFER_ERROR:" . $raw);
            throw new \Exception("transfer error");
        }
        
        return $result;
    }

    // 代付回调通知验证
    public function transfer_notify_verify() {
        $returnArray = array( // 返回字段
            "out_trade_no" => request("out_trade_no"), // 商户订单号
            "order_no" =>  request("order_no"), // 系统平台订单号
            "status" =>  request("status"), // 订单状态
            "money" =>  request("money"), // 订单金额
            "remark" =>  request("remark"), // 系统备注
            "notify_time" => request("notify_time"), // 回调时间
            "is_supp" => request("is_supp"), // 是否补单
        );
        Log::debug("GTPAY_TRNASFER_NOTIFY_REQUEST:", $returnArray);

        $sign = $this->build_sign($returnArray);

        if ($sign == request("sign")) {
            $status = request("status");
            if ($status == "1") {
                    $order_no = request("out_trade_no");
                    $msg = "GTPAY_TRNASFER_SUCCESS:订单号: " . $order_no;
                    Log::info($msg);
                    return $order_no;
            } else {
                Log::debug("GTPAY_TRNASFER_NOTIFY_ERROR:RETURN CODE is " . $status);
                throw new \Exception("return code is " . $status);    
            }
        } else {
            Log::debug("GTPAY_TRNASFER_NOTIFY_ERROR:SIGN ERROR");
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