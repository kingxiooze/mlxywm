<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// CSPay服务类
class CSPay
{

    protected $config;

    public function __construct()
    {
        $this->config = config("payment.cspay");
    }

    // 代收(支付)
    // (银行)代(替商户)收(款)
    public function pay($order) {
        $url = "https://cspay.link/Pay_Index.html";
        $data = [
            "pay_memberid" => $this->config["mid"],
            "pay_orderid" => $order->order_no,
            "pay_applydate" => date("Y-m-d H:i:s"),
            "pay_notifyurl" => url("api/payment/notify/pay/cspay", [], true),
            "pay_callbackurl" => url("api/payment/notify/pay/cspay", [], true),
            "pay_amount" => $order->price,
        ];

        // 生成签名
        $sign = $this->build_sign($data);
        $data["pay_md5sign"] = $sign;
        $data['pay_productname'] = 'Recharge';
        $data['banktype'] = '3';
        $response = Http::asForm()
            ->post($url, $data);
        $html = $response->getBody()->getContents();

        $re = '/(?<=location.href=")[^"]+/m';
        preg_match_all($re, $html, $matches, PREG_SET_ORDER, 0);
        if($matches[0][0]){
            return $matches[0][0];
        }else{
            Log::info("CSPAY_PAY_ERROR:" . $html);
            throw new \Exception("pay error");    
        }

    }

    // 代收回调通知验证
    public function pay_notify_verify() {
        $returnArray = array( // 返回字段
            "memberid" => request("memberid"), // 商户ID
            "orderid" =>  request("orderid"), // 订单号
            "amount" =>  request("amount"), // 交易金额
            "datetime" =>  request("datetime"), // 交易时间
            "transaction_id" =>  request("transaction_id"), // 支付流水号
            "returncode" => request("returncode"),
        );
        Log::debug("CSPAY_PAY_NOTIFY_REQUEST:", $returnArray);

        $sign = $this->build_sign($returnArray);

        if ($sign == request("sign")) {
            $returncode = request("returncode");
            if ($returncode == "00") {
                    $order_no = request("orderid");
                    $msg = "CSPAY_NOTIFY_SUCCESS:订单号: " . $order_no;
                    Log::info($msg);
                    return $order_no;
            } else {
                Log::debug("CSPAY_PAY_NOTIFY_ERROR:RETURN CODE is " . $returncode);
                throw new \Exception("return code is " . $returncode);    
            }
        } else {
            Log::debug("CSPAY_PAY_NOTIFY_ERROR:SIGN ERROR");
            throw new \Exception("sign error");
            
        }
    }

    // 代付(提现/转账)
    // (银行)代(替商户)付(款)
    public function transfer($withdrawl) {
        if (empty($withdrawl->bankcard)){
            throw new \Exception("bank card info is error");  
        }
        $url = "https://cspay.link/Payment_Dfpay_add.html";
        $data = [
            "mchid" => $this->config["mid"],
            "out_trade_no" => $withdrawl->withdrawal_no,
            "money" => $withdrawl->amount,
            "bankname" => "BANK",
            "accountname" => $withdrawl->bankcard->bank_name,
            "cardnumber" => $withdrawl->bankcard->card_no,
            "subbranch" => $withdrawl->bankcard->ifsc_code,
            "pay_notifyurl" => url("api/payment/notify/transfer/cspay", [], true),
            "province" => $withdrawl->bankcard->email,
            "city" => $withdrawl->bankcard->mobile
        ];
        // 生成签名
        $sign = $this->build_sign($data);
        $data["pay_md5sign"] = $sign;

        $response = Http::asForm()
            ->post($url, $data);
        $raw = $response->getBody()->getContents();
        try {
            $result = json_decode($raw, true);
        } catch (\Throwable $th) {
            Log::info("CSPAY_TRANSFER_ERROR:" . $raw);
            throw new \Exception("transfer error");
        }
        
        return $result;
    }

    // 代付回调通知验证
    public function transfer_notify_verify() {
        $returnArray = array( // 返回字段
            "memberid" => request("memberid"), // 商户ID
            "orderid" =>  request("orderid"), // 订单号
            "amount" =>  request("amount"), // 交易金额
            "datetime" =>  request("datetime"), // 交易时间
            "transaction_id" =>  request("transaction_id"), // 支付流水号
            "returncode" => request("returncode"),
        );
        Log::debug("CSPAY_TRNASFER_NOTIFY_REQUEST:", $returnArray);

        $sign = $this->build_sign($returnArray);

        if ($sign == request("sign")) {
            $returncode = request("returncode");
            if ($returncode == "00") {
                    $order_no = request("orderid");
                    $msg = "CSPAY_TRNASFER_SUCCESS:订单号: " . $order_no;
                    Log::info($msg);
                    return $order_no;
            } else {
                Log::debug("CSPAY_TRNASFER_NOTIFY_ERROR:RETURN CODE is " . $returncode);
                throw new \Exception("return code is " . $returncode);    
            }
        } else {
            Log::debug("CSPAY_TRNASFER_NOTIFY_ERROR:SIGN ERROR");
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