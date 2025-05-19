<?php

use App\Http\Controllers\CaptchaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AwardController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CustomerServiceController;
use App\Http\Controllers\FianceRateController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PayController;
use App\Http\Controllers\PayNotifyController;
use App\Http\Controllers\PublicNoticeController;
use App\Http\Controllers\RedPackController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ScrollController;
use App\Http\Controllers\SignLogController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TextContentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\PayChannelController;
use App\Http\Controllers\SMSController;
use App\Http\Controllers\ItemCategoryController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\PrizeWheelController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\YoutubeLinkController;
use App\Http\Controllers\RedpacketsV2Controller;
use App\Http\Controllers\InviteRewardController;
use App\Http\Controllers\TaskOrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix("tools")->group(function(){
    Route::get("captcha", [CaptchaController::class, "getCaptcha"]);
    Route::get("rate", [FianceRateController::class, "index"]);
    Route::middleware("auth:api")->group(function(){
        Route::post("upload/local", [UploadController::class, "local"]);
        Route::post("upload/oss", [UploadController::class, "oss"]);
    });
    Route::post("sms/send", [SMSController::class, "postSend"]);
    Route::get("getUrl", [SMSController::class, "getUrl"]);
    Route::get("saomiaourl", [SMSController::class, "saomiaourl"]);
    Route::get("ipurl", [SMSController::class, "ipurl"]);
});

Route::prefix("taskorder")->group(function(){
   
    Route::get("timersData", [TaskOrderController::class, "timersData"]);
    Route::get("payUsdt", [TaskOrderController::class, "payUsdt"]);
    Route::get("detail", [TaskOrderController::class, "detail"]);
    Route::get("detailorder", [TaskOrderController::class, "detailorder"]);
    
    Route::middleware("auth:api")->group(function(){
        
        Route::get("getList", [TaskOrderController::class, "getList"]);
        Route::get("getState", [TaskOrderController::class, "getState"]);
        
        Route::post("postOrder", [TaskOrderController::class, "postOrder"]);
        Route::post("payorder", [TaskOrderController::class, "payorder"]);
        Route::post("postsureOrders", [TaskOrderController::class, "postsureOrders"]);
        
        
    });
   
    
});



Route::prefix("auth")->group(function(){
    Route::post("register", [AuthController::class, "postRegister"]);
    Route::post("login", [AuthController::class, "postLogin"]);
    Route::post("email/register", [AuthController::class, "postEmailRegister"]);
    Route::post("email/login", [AuthController::class, "postEmailLogin"]);
    Route::post("email/code/send", [AuthController::class, "postEmailSendCode"]);
    Route::post("logout", [AuthController::class, "postLogout"]);
});

Route::prefix("user")->group(function(){
    Route::get("getmvlist", [UserController::class, "getmvlist"]); 
    Route::middleware("auth:api")->group(function(){
        
        Route::post("postaddress", [UserController::class, "postaddress"]);
        Route::get("getaddress", [UserController::class, "getaddress"]);
        Route::get("my/getTransferring", [UserController::class, "getTransferring"]);
        Route::get("my/getBuyList", [UserController::class, "getBuyList"]);
        Route::post("my/postUserTransferring", [UserController::class, "postUserTransferring"]);
        Route::post("my/postUserBalanceTf", [UserController::class, "postUserBalanceTf"]);
        Route::get("team/getcashbacklist", [TeamController::class, "getTeamCashback"]);
        Route::get("team/cashbackstate", [TeamController::class, "getTodayCashbackState"]);
        Route::get("team/state", [TeamController::class, "getTeamState"]);
        Route::get("team/inferior", [TeamController::class, "getTeamInferior"]);
        Route::get("team/inferior/state", [TeamController::class, "getTeamInferiorState"]);
        Route::get("team/teamfor/state", [TeamController::class, "getTeamforState"]);
        Route::get("team/commission", [TeamController::class, "getTeamCommission"]);
        Route::get("team/statementbonus/setting", [TeamController::class, "getTeamStatementBonusSetting"]);
        Route::get("my/income/record", [UserController::class, "getMyIncomeRecord"]);
        Route::get("my/income/state", [UserController::class, "getMyIncomeState"]);
        Route::get("my/device/list", [UserController::class, "getMyDevice"]);
        Route::post("password/change", [UserController::class, "postChangePassword"]);
        Route::post("password/trade/change", [UserController::class, "postChangeTradePassword"]);
        Route::post("bankcard/cu", [UserController::class, "postCUBankcard"]);
        Route::get("bankcard/list", [UserController::class, "getMyBankcard"]);
        Route::get("my/info", [UserController::class, "getMyInfo"]);
        Route::post("my/info", [UserController::class, "postEditMyInfo"]);
        Route::get("info", [UserController::class, "getUserInfo"]);
        Route::get("my/moneylog", [UserController::class, "getMyMoneylog"]);
        Route::get("my/moneylog/state", [UserController::class, "getMyMoneyState"]);
        Route::get("my/withdrawallog", [UserController::class, "getMyWithdrawalLog"]);
        Route::get("my/state/personal", [UserController::class, "getMyPersonalState"]);
        Route::get("my/state/detail", [UserController::class, "getMyDetailState"]);
        Route::get("activite/status", [UserController::class, "getActiviteStatus"]);
        Route::post("activite", [UserController::class, "postActiviteUser"]);
        Route::get("realname", [UserController::class, "getRealname"]);
        Route::post("realname", [UserController::class, "postRealname"]);
        Route::get("my/rechargelog", [UserController::class, "getMyRechargeLog"]);
        Route::get("my/coupon/list", [CouponController::class, "getMyCouponList"]);
        Route::get("my/state/withdrawal", [UserController::class, "getMyWithdrawalState"]);
    });
});

Route::prefix("award")->group(function(){
    Route::middleware("auth:api")->group(function(){
        Route::get("list", [AwardController::class, "getAwardList"]);
        Route::post("receive/invite", [AwardController::class, "postReceiveInvite"]);
        Route::post("receive/item", [AwardController::class, "postReceiveItem"]);
        Route::get("stat/recharge", [AwardController::class, "getRechargeCashbackStat"]);
        Route::post("receive/recharge", [AwardController::class, "postRechargeCashbackReceive"]);
        Route::get("list/recharge", [AwardController::class, "getRechargeCashbackLog"]);
    });
});

Route::prefix("banner")->group(function(){
    Route::get("list", [BannerController::class, "getList"]);
});

Route::prefix("notice")->group(function(){
    Route::get("newest", [PublicNoticeController::class, "getNewest"]);
});

Route::prefix("item")->group(function(){
    Route::get("list", [ItemController::class, "getList"]);
    Route::get("detail", [ItemController::class, "getDetail"]);
    Route::get("price/log", [ItemController::class, "getItemPriceLog"]);
    Route::middleware("auth:api")->group(function(){
        Route::post("buy", [ItemController::class, "postBuy"]);
        // Route::post("sell", [ItemController::class, "postSell"]);
    });
    Route::prefix("review")->group(function(){
        Route::get("tmpl/random", [ReviewController::class, "getRandomTmpl"]);
        Route::get("list", [ReviewController::class, "getList"]);
        
        Route::middleware("auth:api")->group(function(){
            Route::post("new", [ReviewController::class, "postNew"]);
        });
    });
    Route::prefix("category")->group(function(){
        Route::get("list", [ItemCategoryController::class, "getList"]);
    });
    Route::prefix("earning")->group(function(){
        Route::middleware("auth:api")->group(function(){
            Route::post("gain", [ItemController::class, "postGainItemEarning"]);
        });
        
    });
});

Route::prefix("signin")->group(function(){
    Route::middleware("auth:api")->group(function(){
        Route::get("log", [SignLogController::class, "getLog"]);
        Route::post("signin", [SignLogController::class, "postSignIn"]);
        
    });
});

Route::prefix("payment")->group(function(){
    Route::post("recharge", [PayController::class, "postRecharge"]);
    Route::middleware("auth:api")->group(function(){
        Route::post("getOrderInfo", [PayController::class, "getOrderInfo"]);
        Route::post("withdrawal", [PayController::class, "postWithdrawal"]);
        Route::post("convert", [PayController::class, "postConvertToMission"]);
    });
    Route::prefix("notify")->group(function(){
        Route::post("pay/cspay", [PayNotifyController::class, "postPayCspay"]);
        Route::post("transfer/cspay", [PayNotifyController::class, "postTransferCspay"]);
        Route::post("pay/ytpay", [PayNotifyController::class, "postPayYtpay"]);
        Route::post("transfer/ytpay", [PayNotifyController::class, "postTransferYtpay"]);
        Route::post("pay/gspay", [PayNotifyController::class, "postPayGspay"]);
        Route::post("transfer/gspay", [PayNotifyController::class, "postTransferGspay"]);
        Route::post("pay/wepay", [PayNotifyController::class, "postPayWepay"]);
        Route::post("transfer/wepay", [PayNotifyController::class, "postTransferWepay"]);
        Route::post("pay/dfpay", [PayNotifyController::class, "postPayDfpay"]);
        Route::post("transfer/dfpay", [PayNotifyController::class, "postTransferDfpay"]);
        Route::post("pay/sharkpay", [PayNotifyController::class, "postPaySharkpay"]);
        Route::post("transfer/sharkpay", [PayNotifyController::class, "postTransferSharkpay"]);
        Route::post("pay/gtpay", [PayNotifyController::class, "postPayGtpay"]);
        Route::post("transfer/gtpay", [PayNotifyController::class, "postTransferGtpay"]);
        Route::post("pay/ppay", [PayNotifyController::class, "postPayPpay"]);
        Route::post("transfer/ppay", [PayNotifyController::class, "postTransferPpay"]);
        Route::post("pay/mpay", [PayNotifyController::class, "postPayMpay"]);
        Route::post("transfer/mpay", [PayNotifyController::class, "postTransferMpay"]);
        Route::post("pay/ffpay", [PayNotifyController::class, "postPayFfpay"]);
        Route::post("transfer/ffpay", [PayNotifyController::class, "postTransferFfpay"]);
        Route::post("pay/xdpay/{mch}", [PayNotifyController::class, "postPayXdpay"]);
        Route::post("transfer/xdpay/{mch}", [PayNotifyController::class, "postTransferXdpay"]);
        Route::post("pay/wowpay", [PayNotifyController::class, "postPayWowpay"]);
        Route::post("transfer/wowpay", [PayNotifyController::class, "postTransferWowpay"]);
    });
    Route::get("usdt/channel", [PayController::class, "getUsdtChannel"]);
    Route::prefix("channel")->group(function(){
        Route::get("list", [PayChannelController::class, "getList"]);
    });
    Route::get("withdrawal/newest", [PayController::class, "getNewestWithdrawal"]);
});

Route::prefix("setting")->group(function(){
    Route::get("info", [SettingController::class, "getInfo"]);
});

Route::prefix("scroll")->group(function(){
    Route::get("list", [ScrollController::class, "getList"]);
});

Route::prefix("cs")->group(function(){
    Route::get("list", [CustomerServiceController::class, "getList"]);
});

Route::prefix("redpack")->group(function(){
    Route::middleware("auth:api")->group(function(){
        Route::post("open", [RedPackController::class, "postOpen"]);
        Route::get("log/invite", [RedPackController::class, "getInvitePackLog"]);
        Route::get("log/freeze", [RedPackController::class, "getFreezePackLog"]);
        Route::get("total/amount", [RedPackController::class, "getTotalAmount"]);
        Route::post("receive", [RedPackController::class, "postConvertToMission"]);
    });
});

Route::prefix("text")->group(function(){
    Route::get("list", [TextContentController::class, "getList"]);
});

Route::prefix("chat")->group(function(){
    Route::middleware("auth:api")->group(function(){
        Route::get("myroom", [ChatController::class, "getMyRoom"]);
        Route::get("room/user", [ChatController::class, "getRoomUser"]);
        
        Route::post("mute", [ChatController::class, "postMuteMember"]);
        Route::get("records", [ChatController::class, "getRoomChatRecord"]);
        Route::post("send", [ChatController::class, "postSend"]);
        Route::post("redpacket/open", [ChatController::class, "postOpenChatRedPacket"]);
        Route::post("room/create", [ChatController::class, "postCreateRoom"]);
        Route::post("invite", [ChatController::class, "postInviteChat"]);
        Route::post("remove", [ChatController::class, "postRemoveUser"]);
        Route::post("room/info/change", [ChatController::class, "postChangeRoomInfo"]);
        Route::post("room/record/remove/batch", [ChatController::class, "postBatchRemoveRecord"]);
        Route::get("room/info", [ChatController::class, "getRoomInfo"]);
    });
});

Route::prefix("news")->group(function(){
    Route::get("list", [NewsController::class, "getList"]);
});

Route::prefix("coupon")->group(function(){
    Route::middleware("auth:api")->group(function(){
        Route::post("receive", [CouponController::class, "postReceive"]);
    });
});

Route::prefix("state")->group(function(){
    Route::get("platform", [StateController::class, "getPlatformInfo"]);
    Route::get("withdrawal/list", [StateController::class, "getSuccessWithdrawalList"]);
    Route::get("recharge/list", [StateController::class, "getSuccessRechargeList"]);
    Route::get("recharge/invite/lb", [StateController::class, "getInviteRechargeLB"]);
});

Route::prefix("youtubelink")->group(function(){
    Route::get("list", [YoutubeLinkController::class, "getList"]);
    Route::middleware("auth:api")->group(function(){
        Route::post("new", [YoutubeLinkController::class, "postNew"]);
        Route::get("my/list", [YoutubeLinkController::class, "getMyList"]);
    });
});

Route::prefix("prize")->group(function(){
    Route::get("reward/list", [PrizeWheelController::class, "getRewardList"]);
    Route::middleware("auth:api")->group(function(){
        Route::post("turn", [PrizeWheelController::class, "postTurn"]);
        Route::get("my/log", [PrizeWheelController::class, "getMyLog"]);
    });
});

Route::prefix("redpacketv2")->group(function(){
    Route::middleware("auth:api")->group(function(){
        Route::post("open", [RedpacketsV2Controller::class, "postOpen"]);
        Route::get("list", [RedpacketsV2Controller::class, "getList"]);
        Route::get("log", [RedpacketsV2Controller::class, "getLog"]);
    });
});

Route::prefix("invitereward")->group(function(){
    Route::middleware("auth:api")->group(function(){
        Route::get("list", [InviteRewardController::class, "getList"]);
        Route::post("receive", [InviteRewardController::class, "postReceive"]);
    });
});
