<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Dcat\Admin\Admin;

use App\SalesAdmin\Controllers\UserController;
use App\SalesAdmin\Controllers\UserWithdrawalController;
use App\SalesAdmin\Controllers\ReviewRecordController;
use App\SalesAdmin\Controllers\MoneyLogController;
use App\SalesAdmin\Controllers\UserBankcardController;
use App\SalesAdmin\Controllers\OrderController;
use App\SalesAdmin\Controllers\TaskModelController;
use App\SalesAdmin\Controllers\TaskOrderController;
use App\SalesAdmin\Controllers\TaskIndexController;
use App\SalesAdmin\Controllers\ItemController;
use App\SalesAdmin\Controllers\ItemCategoryController;
use App\SalesAdmin\Controllers\TextContentController;
use App\SalesAdmin\Controllers\UserPhoneController;
use App\SalesAdmin\Controllers\UserSfenController;

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

    $router->resource('user_withdrawal', UserWithdrawalController::class);
    $router->resource('review_record', ReviewRecordController::class);
    $router->resource('moneylog', MoneyLogController::class);
    $router->resource("user_bankcard", UserBankcardController::class);
    $router->resource("user_order", OrderController::class);
    $router->resource("filterphone", UserPhoneController::class);
    $router->resource("usersfen", UserSfenController::class);
    
    
    $router->resource("taskmodel", TaskModelController::class);
    $router->resource("taskorder", TaskOrderController::class);
    $router->resource("taskindex", TaskIndexController::class);
    $router->resource('item', ItemController::class);
    $router->resource('item_category', ItemCategoryController::class);
    $router->resource('textcontent', TextContentController::class);
    
    
});
