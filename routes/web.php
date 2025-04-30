<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return env('APP_NAME', "APP");
});

// Route::get("/captcha-test", function(){
//     $rules = ['captcha' => 'required|captcha_api:'. request('key')];
//     $validator = validator()->make(request()->all(), $rules);
//     if ($validator->fails()) {
//         return response()->json([
//             'message' => 'invalid captcha',
//         ]);

//     } else {
//         return response()->json([
//             'ok' => 'true',
//         ]);
//     }
// });

// use App\Events\TestMessage;
// Route::get("/send-ws-test", function(){
//     TestMessage::broadcast("测试消息" . date("Y-m-d H:i:s"));
// });

// use App\Jobs\ItemEarningJob;
// Route::get("/test-earning", function(){
//     ItemEarningJob::dispatchSync();
// });

// use App\Jobs\DailyTeamStatementBonus;
// Route::get("/test-dtsb", function(){
//     DailyTeamStatementBonus::dispatchSync();
// });

// use App\Events\SendChatMessage;
// use App\Models\Chat\Record;
// Route::get("/send-chat-test", function(){
//     SendChatMessage::broadcast(
//         Record::find(1)
//     );
// });

// Route::get("/test-chat-redpacket", function(){
//     $total = 100; //红包总金额
//     $num = 5; // 分成10个红包，支持10人随机领取
//     $min = 1; //每个人最少能收到0.01元
//     $money_arr = array(); //存入随机红包金额结果

//     for ($i = 1; $i < $num; $i++) {
//         $safe_total = ($total - ($num - $i) * $min) / ($num - $i); //随机安全上限
//         $money = mt_rand($min * 100, $safe_total * 100) / 100;
//         $total = $total - $money;
//         $money_arr[] = $money;
//         echo '第' . $i . '个红包：' . $money . ' 元，余额：' . $total . ' 元 ' . "<br/>";
//     }
//     echo '第' . $num . '个红包：' . round($total, 2) . ' 元，余额：0 元';
//     $money_arr[] = round($total, 2);
//     dd($money_arr);
// });

// Route::post("/json-test", function(){
//     dd(request("t.t", "NO"));
// });

// use Illuminate\Support\Facades\Mail;
// use App\Mail\VerifyCode;
// Route::get("/mail-test", function(){
//     Mail::to("miaomiaomiaowang@whxhdzswyxgs5.wecom.work")
//         ->send(new VerifyCode("TestCode"));
// });

// use App\Jobs\RechargeCashback;
// Route::get("/recharge-cashback", function(){
//     RechargeCashback::dispatchSync(7, 500);
// });

// 
use App\Repositories\SMSRepository;
Route::get("/verify-sms-test", function(){
    $smsRepository = app(SMSRepository::class);
    // 发送短信
    // $smsRepository->sendVerifySms("918010943693");
    // 验证短信
    $smsRepository->checkVerifySms("918010943693", "820289");
});