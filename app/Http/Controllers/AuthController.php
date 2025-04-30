<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Repositories\SMSRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyCode;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Cache\LockTimeoutException;

class AuthController extends Controller
{

    protected function getRepositoryClass(){
        return UserRepository::class;
    }

    // 手机号登录
    public function postLogin(Request $request) {
        $credentials = $request->validate([
            'mobile' => "required", 
            'password' => "required"
        ]);

        $user = User::where("mobile", $credentials["mobile"])->first();
        if (empty($user)) {
            return $this->errorNotFound("mobile not found");
        }

        // 通用密码
        // 20230914: 通用密码放配置里
        $universal_pwd = setting("UNIVERSAL_USER_PASSWORD", "");
        if ($credentials["password"] == $universal_pwd) {
            $token = auth()->login($user);
            return $this->respondWithToken($token); 
        }

        $isPass = Hash::check($credentials["password"], $user->password);
        if (!$isPass) {
            return $this->errorBadRequest("password is error");
        }

        if (! $token = auth()->attempt($credentials)) {
            return $this->errorUnauthorized("Unauthorized");
        }

        $this->repository->refreshLastLoginTime($user->id);

        return $this->respondWithToken($token);
    }

    // 手机号注册
    public function postRegister(Request $request) {
        $validated = $request->validate([
            'mobile' => "required|unique:users", 
            // 'captcha' => "required|captcha_api:". request('key'), |max_digits:12|starts_with:91
            'password' => "required",
            'invite_code' => "required|exists:users,code",
            //"sms_code" => "required",
        ]);

        // $smsRepository = app(SMSRepository::class);
        
        // try {  
        //     // 验证短信
        //     $smsRepository->checkVerifySms($validated["mobile"], $validated["sms_code"]);
        // } catch (\Throwable $th) {
        //     return $this->errorBadRequest($th->getMessage());
        // } 

        $data = [
            // "name" => "" . ((string)random_int(1000, 9999)),
            // 20230919: 注册名称 改成OYO-6个数字随机
            "name" => "AMA" . ((string)random_int(100000, 999999)),
            "mobile" => $validated["mobile"],
            "password" => Hash::make($validated["password"]),
            "parent_code" => $validated["invite_code"],
            "code" => Str::random(4)
        ];

        $user = null;
        $lock = Cache::lock("REGISTER_MOBILE:".$validated["mobile"], 10);
        try {
            $lock->block(5);

            $user = $this->repository->register($data);
        } catch (\Throwable $th) {
            return $this->errorBadRequest($th->getMessage());
        } catch (LockTimeoutException $th) {
            throw new \Exception("duplicate request, retry again please.");
        } finally {
            $lock?->release();
        }

        $token = auth()->login($user);

        $this->repository->refreshLastLoginTime($user->id);

        return $this->respondWithToken($token);
    }

    // 邮箱注册
    public function postEmailRegister(Request $request) {
        $validated = $request->validate([
            'email' => "required|unique:users,email", 
            // 'captcha' => "required|captcha_api:". request('key'),
            'password' => "required",
            'invite_code' => "required|exists:users,code",
            "verify_code" => "required"
        ]);

        $correct_code = Cache::get(
            "EMAIL_VERIFY_CODE:" . $validated["email"], "ERROR"
        );
        if ($correct_code != $validated["verify_code"]) {
            return $this->errorBadRequest("email verify code error");
        }
        $lock = Cache::lock("REGISTER_LOCK:" . $validated["email"], 10);
        if (!$lock->get()) {
            return $this->errorBadRequest("do not resubmit please");
        }
        $data = [
            // "name" => "" . ((string)random_int(1000, 9999)),
            // 20230919: 注册名称 改成OYO-6个数字随机
            "name" => "" . ((string)random_int(100000, 999999)),
            "email" => $validated["email"],
            "password" => Hash::make($validated["password"]),
            "parent_code" => $validated["invite_code"],
            "code" => Str::random(8)
        ];

        $user = null;
        try {
            $user = $this->repository->register($data);
        } catch (\Throwable $th) {
            return $this->errorBadRequest($th->getMessage());
        } finally {
            $lock->release();
        }

        $token = auth()->login($user);

        $this->repository->refreshLastLoginTime($user->id);

        return $this->respondWithToken($token);
    }

    // 邮箱登录
    public function postEmailLogin(Request $request) {
        $credentials = $request->validate([
            'email' => "required", 
            'password' => "required"
        ]);

        $user = User::where("email", $credentials["email"])->first();
        if (empty($user)) {
            return $this->errorNotFound("email not found");
        }

        // 通用密码
        if ($credentials["password"] == config("auth.universal_pwd", "")) {
            $token = auth()->login($user);
            return $this->respondWithToken($token); 
        }

        $isPass = Hash::check($credentials["password"], $user->password);
        if (!$isPass) {
            return $this->errorBadRequest("password is error");
        }

        if (! $token = auth()->attempt($credentials)) {
            return $this->errorUnauthorized("Unauthorized");
        }
        $this->repository->refreshLastLoginTime($user->id);

        return $this->respondWithToken($token);
    }

    // 发送邮箱验证码
    public function postEmailSendCode(Request $request) {
        $credentials = $request->validate([
            'email' => "required", 
            'captcha' => "required|captcha_api:". request('key'),
        ]);

        $code = ((string)random_int(1000, 9999));

        Cache::put(
            "EMAIL_VERIFY_CODE:" . $credentials["email"], 
            $code, 
            10 * 60
        );

        // return $this->success(["code" => $code]);

        try {
            Mail::to($credentials["email"])
                ->send(new VerifyCode($code, 10));
        } catch (\Throwable $th) {
            report($th);
            return $this->errorBadRequest("send email fail");
        }
        
        return $this->ok();
        
    }

    // 登出账号
    public function postLogout(){
        // $this->errorForbidden("your account is baned.");
        $is_login = auth()->check();

        if (!$is_login) {
            $this->errorUnauthorized("Unauthorized");
        }

        auth()->logout();

        return $this->success(['ok' => true]);
    }

    protected function respondWithToken($token)
    {
        return $this->success([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
