<?php

use App\Admin\Controllers\BannerController;
use App\Admin\Controllers\CustomerServiceController;
use App\Admin\Controllers\ItemCategoryController;
use App\Admin\Controllers\ItemController;
use App\Admin\Controllers\MoneyLogController;
use App\Admin\Controllers\OrderController;
use App\Admin\Controllers\OrderUsdtController;
use App\Admin\Controllers\PublicNoticeController;
use App\Admin\Controllers\ReviewRecordController;
use App\Admin\Controllers\ReviewTmplController;
use App\Admin\Controllers\UserController;
use App\Admin\Controllers\SettingController;
use App\Admin\Controllers\TextContentController;
use App\Admin\Controllers\UserActiviteCodeController;
use App\Admin\Controllers\UserItemController;
use App\Admin\Controllers\UserWithdrawalController;
use App\Admin\Controllers\UserReadpackController;
use App\Admin\Controllers\WithdrawalsUsdtReceiptController;
use App\Admin\Controllers\UserRealnameController;
use App\Admin\Controllers\NewsController;
use App\Admin\Controllers\PayChannelController;
use App\Admin\Controllers\UserBankcardController;
use App\Admin\Controllers\CouponController;
use App\Admin\Controllers\UserYoutubeLinkController;
use App\Admin\Controllers\PrizeWheelRewardController;
use App\Admin\Controllers\RedpacketsV2Controller;
use App\Admin\Controllers\RedpacketsV2LogController;
use App\Admin\Controllers\WhitelistController;
use App\Admin\Controllers\InviteRewardController;
use App\Admin\Controllers\TaskIndexController;
use App\Admin\Controllers\TaskModelController;
use App\Admin\Controllers\TaskOrderController;
use App\Admin\Controllers\TextContentsController;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Dcat\Admin\Admin;

Admin::routes();

Route::group([
    'prefix'     => config('admin.route.prefix'),
    'namespace'  => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'StateController@index');
    $router->resource('user', UserController::class);
    // 注册用户
    $router->resource('user_all', UserController::class);
    // 有效用户
    $router->resource('user_has_recharge', UserController::class);
    // 无效用户
    $router->resource('user_donthave_recharge', UserController::class);
    // 用户统计
    $router->resource('user_state', UserController::class);

    $router->resource('banner', BannerController::class);
    $router->resource('setting', SettingController::class);
    $router->resource('notice', PublicNoticeController::class);
    $router->resource('item', ItemController::class);
    $router->resource('moneylog', MoneyLogController::class);
    $router->resource('order', OrderController::class);
    $router->resource('order_usdt', OrderUsdtController::class);
    $router->resource('user_withdrawal', UserWithdrawalController::class);
    $router->resource('service_customer', CustomerServiceController::class);
    $router->resource('user_item', UserItemController::class);
    $router->resource('review_tmpl', ReviewTmplController::class);
    $router->resource('review_record', ReviewRecordController::class);
    $router->resource('user_redpack', UserReadpackController::class);
    $router->resource('text_content', TextContentController::class);
    $router->resource('user_activite_code', UserActiviteCodeController::class);
    $router->resource('item_category', ItemCategoryController::class);
    $router->resource('withdrawals_usdt_receipt', WithdrawalsUsdtReceiptController::class);
    $router->resource("user_realname", UserRealnameController::class);
    $router->resource("news", NewsController::class);
    $router->resource("pay_channels", PayChannelController::class);
    $router->resource("user_bankcard", UserBankcardController::class);
    $router->resource("coupon", CouponController::class);
    $router->resource("youtube_link", UserYoutubeLinkController::class);
    $router->resource("prize_wheel_reward", PrizeWheelRewardController::class);
    $router->resource("redpacket_v2", RedpacketsV2Controller::class);
    $router->resource("whitelist", WhitelistController::class);
    $router->resource("invite_reward", InviteRewardController::class);
    $router->resource("redpackets_v2_log", RedpacketsV2LogController::class);
    $router->resource("taskindex", TaskIndexController::class);
    $router->resource("taskorder", TaskOrderController::class);
    $router->resource("taskmodel", TaskModelController::class);
    $router->resource("textcontents", TextContentsController::class);
});
