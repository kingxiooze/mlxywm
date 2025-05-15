<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// FFPay服务类
class FFPay
{

    protected $config;

    public function __construct()
    {
        $this->config = config("payment.ffpay");
    }

    // 代收(支付)
    // (银行)代(替商户)收(款)
    public function pay($order,$code) {
        $url = "https://wg.gtrpay001.com/collect/create";
        $data = [
          
            "mchId" => $this->config["mid"],
            "passageId" =>$code,
            "orderAmount" => $order->price,
            "orderNo" =>$order->order_no,
            "notifyUrl" => url("api/payment/notify/pay/ffpay", [], true),
            //"callBackUrl" => url("api/payment/notify/pay/ffpay", [], true),
            
           
            
            // "order_date" => date("Y-m-d H:i:s"),
            // "goods_name" => "Balance Recharge",
        ];
        Log::info("FFPAY_PAY_ERROR:" . $code);
        // 生成签名
        $sign = $this->build_sign($data);
        $data["sign"] = $sign;
        //$data['sign_type'] = 'MD5';

        $response = Http::asForm()
            ->post($url, $data);
        $raw = $response->getBody()->getContents();
        try {
            $result = json_decode($raw, true);
        } catch (\Throwable $th) {
            Log::info("FFPAY_PAY_ERROR:" . $raw);
            throw new \Exception("pay error");
        }
        Log::info($result);
        if($result["success"]){
            return $result["data"];
        }else{
            Log::info("FFPAY_PAY_ERROR:" . $result["msg"]);
            throw new \Exception("pay error");    
        }

    }

    // 代收回调通知验证
    public function pay_notify_verify() {
        $returnArray = array( // 返回字段
            "tradeNo" => request("tradeNo"), // 订单状态
            "orderNo" =>  request("orderNo"), // 商户号
            "realAmount" =>  request("realAmount"), // 商家订单号码
            "orderAmount" =>  request("orderAmount"), // 原始订单金额
            "payStatus" =>  request("payStatus"), // 实际支付金额
            "reverse" => request("reverse"), // 订单时间
            "remark" =>  request("remark"), // 平台支付订单号
        );
        
         
        $sign = $this->build_sign($returnArray);
        
        //if ($sign == request("sign")) {
            $returncode = request("payStatus");
            if ($returncode == "1") {
                    $order_no = request("orderNo");
                    $msg = "FFPAY_PAY_NOTIFY_SUCCESS:订单号: " . $order_no;
                    
                    return $order_no;
            } else {
                Log::debug("FFPAY_PAY_NOTIFY_ERROR:RETURN CODE is " . $returncode);
                throw new \Exception("return code is " . $returncode);    
            }
        // } else {
        //     Log::debug("FFPAY_PAY_NOTIFY_ERROR:SIGN ERROR");
        //     throw new \Exception("sign error");
            
        // }
    }

    // 代付(提现/转账)
    // (银行)代(替商户)付(款)
    public function transfer($withdrawl) {
        if (empty($withdrawl->bankcard)){
            throw new \Exception("bank card info is error");  
        }
        $url = "https://api.ffpays.com/pay/transfer";
        $data = [
            "mch_id" => $this->config["mid"],
            "mch_transferId" => $withdrawl->withdrawal_no,
            "transfer_amount" => sprintf("%.0f", $withdrawl->amount),
            "apply_date" => date("Y-m-d H:i:s"),
            "bank_code" => "AGRO",
            "receive_name" =>"AGRO",
            "receive_account" => $withdrawl->bankcard->card_no,
            "remark" => $withdrawl->bankcard->ifsc_code,
            "back_url" => url("api/payment/notify/transfer/ffpay", [], true),
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
            Log::info("FFPAY_TRANSFER_ERROR:" . $raw);
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
        Log::debug("FFPAY_TRNASFER_NOTIFY_REQUEST:", $returnArray);

        $sign = $this->build_sign($returnArray, "transfer_key");

        if ($sign == request("sign")) {
            $returncode = request("respCode");
            if ($returncode == "SUCCESS") {
                    $order_no = request("merTransferId");
                    $msg = "FFPAY_TRNASFER_SUCCESS:订单号: " . $order_no;
                    Log::info($msg);
                    return $order_no;
            } else {
                Log::debug("FFPAY_TRNASFER_NOTIFY_ERROR:RETURN CODE is " . $returncode);
                throw new \Exception("return code is " . $returncode);    
            }
        } else {
            Log::debug("FFPAY_TRNASFER_NOTIFY_ERROR:SIGN ERROR");
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