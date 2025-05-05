<?php

namespace App\Http\Controllers;
use App\Models\MallUrl;
use App\Repositories\SMSRepository;
use Illuminate\Http\Request;

class SMSController extends Controller
{
    protected function getRepositoryClass(){
        return SMSRepository::class;
    }

    // 发送验证码短信
    public function postSend(Request $request) {
        $validated = $request->validate([
            'mobile' => "required", 
            // 'captcha' => "required|captcha_api:". request('key'),
        ]);

        try {
            $this->repository->sendVerifySms($validated["mobile"]);
        } catch (\Throwable $th) {
            return $this->errorBadRequest($th->getMessage());
        }
        
        return $this->ok();
    }
    
    //获取地址
     public function getUrl(Request $request) {
         
         
         return $this->success(MallUrl::where("type",1)->orderBy("wigth","desc")->first());
     }
}
