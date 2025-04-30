<?php
namespace App\Services\SMS;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// Buka短信服务类
class Buka {
    protected $config;

    public function __construct()
    {
        $this->config = config("sms.buka");
    }

    public function send($mobile, $content) {
        $timestamp = time()*1000;
       
        $data = [
            "phone" => $mobile,
            "msg" => $content,
            "timestamp" => $timestamp,
            "sign" => $this->build_sign(["timestamp" => $timestamp]),
            "appkey" => $this->config["api_key"],
            "appcode"=>1000,
            "number"=>2
        ];

      
        $headers = [
            "Content-type"=>"application/json"
            
        ];
       
        $response = Http::withHeaders($headers)
            ->post("http://120.79.68.240:9090/sms/batch/v1", $data);
        $result = $response->json();
        // Log::error($result);
        // return $result;
        if ($result["code"] == 00000) {
            return true;
        } else {
            Log::error("BUKA_SMS_ERROR_RESULT", $result);
            throw new \Exception($result["status"] . "," . $result["reason"]);
        }
    }

    // 生成签名
    protected function build_sign($data) {
        // data中仅要求timestamp一个参数
        $timestamp = Arr::get($data, "timestamp");
        if (empty($timestamp)) {
            throw new \Exception("require timestamp");
        }

        $md5str = $this->config["api_key"] . $this->config["api_secret"] . $timestamp;
        return strtolower(md5($md5str));
    }
}