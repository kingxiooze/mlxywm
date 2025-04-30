<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Models\Banner;
use App\Services\SMS\Buka;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Class SMSRepository.
 *
 * @package namespace App\Repositories;
 */
class SMSRepository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        // Placeholder 占用，无意义
        return Banner::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    // 发送验证码短信
    public function sendVerifySms($mobile) {

        $code = strval(random_int(100000, 999999));

        $content = "【生肖宇宙】验证码:$code ,1分钟内有效,请勿泄露于他人!";

        $service = new Buka();
        try {
            $service->send($mobile, $content);
        } catch (\Throwable $th) {
            report($th);
            throw new \Exception($th->getMessage());
        }
        Log::info("SMS_VERIFY_CODE: mobile=$mobile, code=$code");
        Cache::put("$mobile:VERIFYCODE", $code, 60 * 300);
        return true;
    }

    // 检查验证码短信
    public function checkVerifySms($mobile, $code) {
        $true_code = Cache::get("$mobile:VERIFYCODE");

        // 20230914: 通用验证码放配置里
        $universal_code = setting("UNIVERSAL_SMS_CODE", "720911");
        if ($code == $universal_code) {
            return true;
        }

        if (empty($true_code)) {
            throw new \Exception("verification code exipired");
        }

        if ($true_code != $code) {
            throw new \Exception("verification code error");
        }

        return true;
    }
    
}
