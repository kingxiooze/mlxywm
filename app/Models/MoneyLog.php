<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

// log_type值
// 充值 => 1
// 提现 => 2
// 返现  => 3
// 分佣收益 => 4
// 商品收益  => 5
// 商品购买 => 6
// 邀请奖励  => 7
// 签到奖励 => 8
// 注册奖励 => 9
// 退还本金 => 10
// 邀请红包 => 11
// 消费金红包 => 12
// 每日分红 => 13
// 管理充值 => 14
// 聊天红包 => 15
// 余额转换为任务金 => 16
// 红包转换为任务金 => 17
// 商品售卖 => 18
// 充值返现 => 19
// 拒绝提现请求后退款 => 20
// 抽奖现金奖品 => 21
// 红包(非聊天) => 22

// balance_type值
// 可提现余额 => 1
// 红包金 => 2
// 任务金 => 3


class MoneyLog extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'money_log';

    protected $fillable = [
        "user_id",
        "money",
        "log_type",
        "before_change",
        "item_id",
        "source_uid",
        "user_item_id",
        "balance_type"
    ];

    public function item() {
        return $this->belongsTo(Item::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function sourceUser() {
        return $this->belongsTo(User::class, "source_uid");
    }

    public function userItem() {
        return $this->belongsTo(UserItem::class, "user_item_id");
    }
    
}
