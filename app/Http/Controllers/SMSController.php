<?php

namespace App\Http\Controllers;
use App\Models\MallUrl;
use App\Repositories\SMSRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
     
       //扫描地址
     public function saomiaourl(Request $request) {
         $url = "https://openapi.chinaz.net/v1/1029/check_google";
         
         //查询域名
         $mallurls =  MallUrl::where("type",1)->get();
         $data = [
            "APIKey" => "apiuser_quantity_bdf393c36eaab9e9f597054cda226b13_9189e6399d0f42e695f08c1a4a9a5f83",
            "ChinazVer" => "1.0"
        ];
         
         foreach ($mallurls as $mallurl) {
            
            $data['url']= $mallurl->url;
            $response = Http::get($url, $data);
            $raw = $response->getBody()->getContents();
             $result = json_decode($raw, true);
             if($result["code"]==1002){
                 $mallurl->type = 2;
                 $mallurl->save();
             }
           
         }
         
         
         
         
         
         
         return $this->ok();
     }
     
      //扫描地址
     public function ipurl(Request $request) {
         $mallurls =  MallUrl::where("type",1)->get();
         
         foreach ($mallurls as $mallurl) {
            
            
            $host = parse_url($mallurl->url, PHP_URL_HOST);
            $ip = gethostbyname($host); 
             
              
             if($ip==$host){
                 $mallurl->type = 3;
                 $mallurl->save();
             }
           
         }
       
         
         
         
         
         return $this->success($ip);
     }
}
