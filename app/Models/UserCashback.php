<?php

namespace App\Models;

use App\Repositories\UserRepository;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserCashback extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'user_cashback';

    protected $fillable = [
        "user_id",
        "pay_uid",
        "item_id",
        "pay_amount",
        "back_amount",
        "status",
        "log_type"
    ];
    
    public function pay_user() {
        return $this->belongsTo(User::class, "pay_uid");
    }

    // 20230920: (返现)改成购买后自动触发，和amd 一样
    // 领取返现
    public function receive() {
        $back_amount = $this->back_amount;
        if (floatval($back_amount) > 0) {
            $userRepository = app(UserRepository::class);
            DB::beginTransaction();
            try {
                $log_type = $this->log_type ?? 3;
                // 用户添加余额
                $userRepository->addBalance([
                    "user_id" => $this->user_id,
                    "money" => $back_amount,
                    "log_type" => $log_type,
                    "item_id" => $this->item_id,
                    "source_uid" => $this->pay_uid
                ]);
                // 更新返现领取状态
                $this->status = 1;
                $this->save();
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                throw new \Exception("cashback receive error");
            }
        }
    }
}
