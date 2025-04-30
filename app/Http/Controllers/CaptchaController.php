<?php

namespace App\Http\Controllers;

use Mews\Captcha\Captcha;

class CaptchaController extends Controller
{
    // 获取验证码
    public function getCaptcha(Captcha $captcha) {
        if (ob_get_contents()) {
            ob_clean();
        }
        $data = $captcha->create("default", true);
        return $this->success($data);
    }
}
