<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// SharkPay服务类
class SharkPay
{

    protected $config;

    public function __construct()
    {
        $this->config = config("payment.sharkpay");
    }

    // 代收(支付)
    // (银行)代(替商户)收(款)
    public function pay($order) {
         try {
        $url = "https://api-pkr.onepay.news/api/v1/order/receive";
        $data = [
            "orderNo" =>$order->order_no,
            "payCode" => $this->config["pay_code"],
             "amount" => floatval($order->price*100),
            "notifyUrl" => url("api/payment/notify/pay/sharkpay", [], true),
            "returnUrl" => "https://www.ptcm.in"
        ];
        $headers = [
            "Authorization"=>$this->config["key"],
            "Content-type"=>"application/json"
            
        ];
       
        $method = 'AES-128-CBC';  
        $key = "M986e61H0DOcz3KU"; // 随机生成密钥  
        //$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method)); // 初始化向量  
        $encrypted = bin2hex(openssl_encrypt(json_encode($data), $method, $key, OPENSSL_RAW_DATA, $key));  
        
//$decrypted = openssl_decrypt($encrypted, $method, $key, 0, $iv); 
       $postData = [
           "data"=>$encrypted];
       $response = Http::withHeaders($headers)
            ->post($url, $postData);
            
        //Log::info($response);    
       
            $result = json_decode($response, true);
        } catch (\Throwable $th) {
            Log::info("SHARKPAY_PAY_ERROR:" . $th);
            throw new \Exception("pay error");
        }

        if($result["code"] == "200"){
            $orderinfo = \App\Models\Order::where("id",$order->id)->first();
            $orderinfo->pay_url =  $result["data"]["paymentUrl"];
            $orderinfo->save();
            return $result["data"]["paymentUrl"];
        }else{
            Log::info("SHARKPAY_PAY_ERROR:" . $result["msg"]);
            throw new \Exception("pay error");    
        }

    }

    // 代收回调通知验证
    public function pay_notify_verify() {
        $returnArray = array( // 返回字段
            "merchant_id" => request("data.merchant_id"), // 商户号
            "mer_order_num" =>  request("data.mer_order_num"), // 商户订单号
            "price" =>  request("data.price"), // 订单金额
            "real_price" =>  request("data.real_price"), // 实际支付金额
            "finish_time" =>  request("data.finish_time"), // 完成支付时间
            "order_num" => request("data.order_num"), // 支付平台订单号
            "attach" =>  request("data.attach"), // 商户附加参数
        );
        Log::debug("SHARKPAY_PAY_NOTIFY_REQUEST:", $returnArray);

        $sign = $this->build_sign($returnArray);

        if ($sign == request("data.sign")) {
            $returncode = request("code");
            if ($returncode == "200") {
                $order_no = request("data.mer_order_num");
                $msg = "SHARKPAY_NOTIFY_SUCCESS:订单号: " . $order_no;
                Log::info($msg);
                return $order_no;
            } else {
                Log::debug("SHARKPAY_PAY_NOTIFY_ERROR:RETURN CODE is " . $returncode);
                throw new \Exception("return code is " . $returncode);    
            }
        } else {
            Log::debug("SHARKPAY_PAY_NOTIFY_ERROR:SIGN ERROR");
            throw new \Exception("sign error");
            
        }
    }

    // 代付(提现/转账)
    // (银行)代(替商户)付(款)
    public function transfer($withdrawl) {
        if (empty($withdrawl->bankcard)){
            throw new \Exception("bank card info is error");  
        }
        $url = "https://api-pkr.onepay.news/api/v1/order/out";
       
       
        $data = [
            "orderNo" => $withdrawl->withdrawal_no,
            "payCode" =>$this->config["pay_code"],
            "amount" => floatval($withdrawl->amount*100),
            "notifyUrl" => url("api/payment/notify/transfer/sharkpay", [], true),
            "payeeType" => $withdrawl->bankcard->bank_name=="Easypaisa"?0:1,
            "payeeName" => $withdrawl->bankcard->name,
            "payeeFirstInfo" => $withdrawl->bankcard->card_no,
            "payeeSecondInfo" => $withdrawl->bankcard->bank_name
        ];
         $headers = [
            "Authorization"=>$this->config["key"],
            "Content-type"=>"application/json"
            
        ];
        // 生成签名
        $method = 'AES-128-CBC';  
        $key = "M986e61H0DOcz3KU"; // 随机生成密钥  
        $encrypted = bin2hex(openssl_encrypt(json_encode($data), $method, $key, OPENSSL_RAW_DATA, $key));  
       $postData = [
           "data"=>$encrypted];
       $response = Http::withHeaders($headers)
            ->post($url, $postData);
       Log::info($response);     
        try {
            $result = json_decode($response, true);
               
        } catch (\Throwable $th) {
            Log::info("SHARKPAY_TRANSFER_ERROR:" . $raw);
            throw new \Exception("transfer error");
        }
        
        return $result;
    }

    // 代付回调通知验证
    public function transfer_notify_verify() {
        $returnArray = array( // 返回字段
            "merchant_id" => request("data.merchant_id"), // 商户号
            "mer_order_num" =>  request("data.mer_order_num"), // 商户订单号
            "price" =>  request("data.price"), // 订单金额
            "finish_time" =>  request("data.finish_time"), // 完成支付时间
            "order_num" => request("data.order_num"), // 支付平台订单号
        );
        Log::debug("SHARKPAY_TRNASFER_NOTIFY_REQUEST:", $returnArray);

        $sign = $this->build_sign($returnArray);

        if ($sign == request("data.sign")) {
            $returncode = request("code");
            if ($returncode == "200") {
                    $order_no = request("data.mer_order_num");
                    $msg = "SHARKPAY_TRNASFER_SUCCESS:订单号: " . $order_no;
                    Log::info($msg);
                    return $order_no;
            } else {
                Log::debug("SHARKPAY_TRNASFER_NOTIFY_ERROR:RETURN CODE is " . $returncode);
                throw new \Exception("return code is " . $returncode);    
            }
        } else {
            Log::debug("SHARKPAY_TRNASFER_NOTIFY_ERROR:SIGN ERROR");
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
        return strtoupper(md5($md5str . "key=" . $this->config[$keyType]));
    }
}