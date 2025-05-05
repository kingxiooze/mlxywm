<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// WOWPay服务类
class WOWPay
{

    protected $config;

    public function __construct()
    {
        $this->config = config("payment.wowpay");
    }

    // 代收(支付)
    // (银行)代(替商户)收(款)
    public function pay($order) {
        $url = "https://pay6de1c7.wowpayglb.com/pay/web";
        $data = [
            "version" => "1.0",
            "mch_id" => $this->config["mid"],
            "notify_url" => url("api/payment/notify/pay/wowpay", [], true),
            "page_url" => url("api/payment/notify/pay/wowpay", [], true),
            "mch_order_no" =>$order->order_no,
            "pay_type" => "151",
            "trade_amount" => $order->price,
            "order_date" => date("Y-m-d H:i:s"),
            "goods_name" => "Balance Recharge",
        ];

        // 生成签名
        $sign = $this->build_sign($data);
        $data["sign"] = $sign;
        $data['sign_type'] = 'MD5';

        $response = Http::asForm()
            ->post($url, $data);
        $raw = $response->getBody()->getContents();
        try {
            $result = json_decode($raw, true);
        } catch (\Throwable $th) {
            Log::info("WOWPAY_PAY_ERROR:" . $raw);
            throw new \Exception("pay error");
        }

        if($result["respCode"] == "SUCCESS"){
            return $result["payInfo"];
        }else{
            Log::info("WOWPAY_PAY_ERROR:" . $result["tradeMsg"]);
            throw new \Exception("pay error");    
        }

    }

    // 代收回调通知验证
    public function pay_notify_verify() {
        $returnArray = array( // 返回字段
            "tradeResult" => request("tradeResult"), // 订单状态
            "mchId" =>  request("mchId"), // 商户号
            "mchOrderNo" =>  request("mchOrderNo"), // 商家订单号码
            "oriAmount" =>  request("oriAmount"), // 原始订单金额
            "amount" =>  request("amount"), // 实际支付金额
            "orderDate" => request("orderDate"), // 订单时间
            "orderNo" =>  request("orderNo"), // 平台支付订单号
        );
        Log::debug("WOWPAY_PAY_NOTIFY_REQUEST:", $returnArray);

        $sign = $this->build_sign($returnArray);

        if ($sign == request("sign")) {
            $returncode = request("tradeResult");
            if ($returncode == "1") {
                    $order_no = request("mchOrderNo");
                    $msg = "WOWPAY_PAY_NOTIFY_SUCCESS:订单号: " . $order_no;
                    Log::info($msg);
                    return $order_no;
            } else {
                Log::debug("WOWPAY_PAY_NOTIFY_ERROR:RETURN CODE is " . $returncode);
                throw new \Exception("return code is " . $returncode);    
            }
        } else {
            Log::debug("WOWPAY_PAY_NOTIFY_ERROR:SIGN ERROR");
            throw new \Exception("sign error");
            
        }
    }

    // 代付(提现/转账)
    // (银行)代(替商户)付(款)
    public function transfer($withdrawl) {
        if (empty($withdrawl->bankcard)){
            throw new \Exception("bank card info is error");  
        }
        $url = "https://pay6de1c7.wowpayglb.com/pay/transfer";
        $data = [
            "mch_id" => $this->config["mid"],
            "mch_transferId" => $withdrawl->withdrawal_no,
            "transfer_amount" => sprintf("%.0f", $withdrawl->amount),
            "apply_date" => date("Y-m-d H:i:s"),
            "bank_code" => "IDPT0001",
            "receive_name" => $withdrawl->bankcard->name,
            "receive_account" => $withdrawl->bankcard->card_no,
            "remark" => $withdrawl->bankcard->ifsc_code,
            "back_url" => url("api/payment/notify/transfer/wowpay", [], true),
        ];
        // 生成签名
        $sign = $this->build_sign($data, "transfer_key");
        $data["sign"] = $sign;
        $data["sign_type"] = "MD5";

        $response = Http::asForm()
            ->post($url, $data);
        $raw = $response->getBody()->getContents();
        try {
            $result = json_decode($raw, true);
        } catch (\Throwable $th) {
            Log::info("WOWPAY_TRANSFER_ERROR:" . $raw);
            throw new \Exception("transfer error");
        }
        
        return $result;
    }

    // 代付回调通知验证
    public function transfer_notify_verify() {
        $returnArray = array( // 返回字段
            "tradeResult" => request("tradeResult"), // 订单状态
            "merTransferId" =>  request("merTransferId"), // 商家转账单号
            "merNo" =>  request("merNo"), // 商户代码
            "tradeNo" =>  request("tradeNo"), // 平台订单号
            "transferAmount" =>  request("transferAmount"), // 代付金额
            "applyDate" => request("applyDate"), // 订单时间
            "version" => request("version"), // 版本号
            "respCode" => request("respCode"), //回调状态
        );
        Log::debug("WOWPAY_TRNASFER_NOTIFY_REQUEST:", $returnArray);

        $sign = $this->build_sign($returnArray, "transfer_key");

        if ($sign == request("sign")) {
            $returncode = request("respCode");
            if ($returncode == "SUCCESS") {
                    $order_no = request("merTransferId");
                    $msg = "WOWPAY_TRNASFER_SUCCESS:订单号: " . $order_no;
                    Log::info($msg);
                    return $order_no;
            } else {
                Log::debug("WOWPAY_TRNASFER_NOTIFY_ERROR:RETURN CODE is " . $returncode);
                throw new \Exception("return code is " . $returncode);    
            }
        } else {
            Log::debug("WOWPAY_TRNASFER_NOTIFY_ERROR:SIGN ERROR");
            throw new \Exception("sign error");
            
        }
    }

    // 生成签名
    protected function build_sign($data, $keyType="pay_key") {
        ksort($data);
        reset($data);
        $md5str = "";
        foreach ($data as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        return strtolower(md5($md5str . "key=" . $this->config[$keyType]));
    }
}