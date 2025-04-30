<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// PTMPay服务类
class PTMPay
{

    protected $config;

    public function __construct()
    {
        $this->config = config("payment.ptmpay");
    }

    // 代收(支付)
    // (银行)代(替商户)收(款)
    public function pay($order) {
        $url = "https://pay.ptmpay.xyz/Pay-payment.aspx";
        $data = [
            "pay_memberid" => $this->config["mid"],
            "pay_orderid" => $order->order_no,
            "pay_applydate" => date("Y-m-d H:i:s"),
            "pay_bankcode" => "ydsep",
            "pay_notifyurl" => url("api/payment/notify/pay/ptmpay", [], true),
            "pay_callbackurl" => url("api/payment/notify/pay/ptmpay", [], true),
            "pay_amount" => $order->price,
        ];

        // 生成签名
        $sign = $this->build_sign($data);
        $data["pay_md5sign"] = $sign;
        $data['return_type'] = 'json';

        $response = Http::asForm()
            ->post($url, $data);
        $result = $response->json();

    
        if($result["returncode"] == "200"){
            return $result["payurl"];
        }else{
            Log::info("PTMPAY_PAY_ERROR:" . $result["returncode"]);
            throw new \Exception("pay error");    
        }

    }

    // 代收回调通知验证
    public function pay_notify_verify() {
        $returnArray = array( // 返回字段
            "memberid" => request("memberid"), // 商户ID
            "orderid" =>  request("orderid"), // 订单号
            "transaction_id" =>  request("transaction_id"), // 支付流水号
            "amount" =>  request("amount"), // 实际支付金额
            "amount_origin" =>  request("amount_origin"), // 申请支付金额
            "datetime" =>  request("datetime"), // 交易时间
            "returncode" => request("returncode"),
        );
        Log::debug("PTMPAY_PAY_NOTIFY_REQUEST:", $returnArray);

        $sign = $this->build_sign($returnArray);

        if ($sign == request("sign")) {
            $returncode = request("returncode");
            if ($returncode == "00") {
                $order_no = request("orderid");
                $msg = "PTMPAY_NOTIFY_SUCCESS:订单号: " . $order_no;
                Log::info($msg);
                return $order_no;
            } else {
                Log::debug("PTMPAY_PAY_NOTIFY_ERROR:RETURN CODE is " . $returncode);
                throw new \Exception("return code is " . $returncode);    
            }
        } else {
            Log::debug("PTMPAY_PAY_NOTIFY_ERROR:SIGN ERROR");
            throw new \Exception("sign error");
            
        }
    }

    // 代付(提现/转账)
    // (银行)代(替商户)付(款)
    public function transfer($withdrawl) {
        if (empty($withdrawl->bankcard)){
            throw new \Exception("bank card info is error");  
        }
        $url = "https://pay.ptmpay.xyz/Pay-payment-draw.aspx";
        $data = [
            "memberid" => $this->config["mid"],
            "orderid" => $withdrawl->withdrawal_no,
            "bankcode" => "ydsep",
            "notifyurl" => url("api/payment/notify/transfer/ptmpay", [], true),
            "amount" => $withdrawl->amount,
            "mobile" => $withdrawl->bankcard->mobile,
            "email" => $withdrawl->bankcard->email,
            "bankname" => $withdrawl->bankcard->bank_name,
            "cardnumber" => $withdrawl->bankcard->card_no,
            "accountname" => $withdrawl->bankcard->name,
            "ifsc" => $withdrawl->bankcard->ifsc_code,
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
            Log::info("PTMPAY_TRANSFER_ERROR:" . $raw);
            throw new \Exception("transfer error");
        }
        
        return $result;
    }

    // 代付回调通知验证
    public function transfer_notify_verify() {
        $returnArray = array( // 返回字段
            "memberid" => request("memberid"), // 商户ID
            "orderid" =>  request("orderid"), // 订单号
            "transaction_id" =>  request("transaction_id"), // 支付流水号
            "amount" =>  request("amount"), // 交易金额
            "datetime" =>  request("datetime"), // 交易时间
            "returncode" => request("returncode"),
        );
        Log::debug("PTMPAY_TRNASFER_NOTIFY_REQUEST:", $returnArray);

        $sign = $this->build_sign($returnArray);

        if ($sign == request("sign")) {
            $returncode = request("returncode");
            $msg = request("msg", "null");
            // if ($returncode == "00") {
                $order_no = request("orderid");
                $msg = "PTMPAY_TRNASFER_SUCCESS:订单号: " . $order_no;
                Log::info($msg);
                return $order_no;
            // } else {
            //     Log::debug("PTMPAY_TRNASFER_NOTIFY_ERROR:RETURN CODE is " . $returncode . ", msg is" . $msg);
            //     throw new \Exception("return code is " . $returncode);    
            // }
        } else {
            Log::debug("PTMPAY_TRNASFER_NOTIFY_ERROR:SIGN ERROR");
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