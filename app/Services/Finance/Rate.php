<?php
namespace App\Services\Finance;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

// 货币汇率服务类

class Rate {
    protected $config;

    public function __construct()
    {
        $this->config = config("services.finance.rate");
    }

    // 获取汇率
    // $source = 原货币单位
    // $target = 目标货币单位
    public function get($source, $target) {
        $cache_key = sprintf("FIANCE_RATE_%s_TO_%s", $source, $target);
        $data = Cache::get($cache_key, null);
        
        if (!empty($data)) {
            return $data;
        }

        $url = "https://sapi.k780.com";

        $query = [
            "app" => "finance.rate",
            "scur" => $source,
            "tcur" => $target,
            "appkey" => $this->config["appkey"],
            "sign" => $this->config["sign"]
        ];

        $response = Http::get($url, $query);

        $data = $response->json();

        if (Arr::get($data, "success", "0") == 1) {
            // 保存一天
            Cache::put($cache_key, $data, 60 * 60 * 24);
        }

        return $data;
    }
}