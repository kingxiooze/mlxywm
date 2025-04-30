<?php

namespace App\Models;

use App\Repositories\UserRepository;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Services\Payment\CSPay;
use App\Services\Payment\YTPay;
use App\Services\Payment\GSPay;
use App\Services\Payment\WEPay;
use App\Services\Payment\DFPay;
use App\Services\Payment\SharkPay;
use App\Services\Payment\GTPay;
use App\Services\Payment\PPay;
use App\Services\Payment\MPay;
use App\Services\Payment\FFPay;
use App\Services\Payment\XDPay;
use App\Services\Payment\WOWPay;
use App\Services\Payment\PTMPay;
use Illuminate\Support\Facades\DB;

class UserWithdrawal extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'user_withdrawals';

    protected $fillable = [
        "user_id",
        "status",
        "bankcard_id",
        "amount",
        "withdrawal_no",
        "transaction_id",
        "pay_type",
        "pay_status",
        "order_status",
        "pay_time",
        "after_balance"
    ];

    public function bankcard(){
        return $this->belongsTo(UserBankcard::class, "bankcard_id");
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    // 线上打款
    public function online_transfer(){
        // if ($this->user->balance < $this->amount) {
        //     admin_error("提现失败", "余额不足");
        // }
        if ($this->pay_type == 1) {
            $service = new CSPay();
            DB::beginTransaction();
            try {
                $result = $service->transfer($this);    
                if($result["status"] == "success") {
                    $this->order_status = 1;
                    $this->save();
                } else {
                    throw new \Exception($result["msg"]);
                }
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                admin_error("提现失败", $th->getMessage());
            }
        } elseif ($this->pay_type == 3) {
            $service = new YTPay();
            DB::beginTransaction();
            try {
                $result = $service->transfer($this);    
                if($result["code"] == 0) {
                    $this->order_status = 1;
                    $this->save();
                } else {
                    throw new \Exception($result["message"]);
                }
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                admin_error("提现失败", $th->getMessage());
            }
        } elseif ($this->pay_type == 4) {
            $service = new GSPay("payment.gspay2");
            DB::beginTransaction();
            try {
                $result = $service->transfer($this);    
                if($result["retCode"] == "SUCCESS") {
                    $this->order_status = 1;
                    $this->save();
                } else {
                    throw new \Exception($result["retMsg"]);
                }
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                admin_error("提现失败", $th->getMessage());
            }
        } elseif ($this->pay_type == 5) {
            $service = new WEPay();
            DB::beginTransaction();
            try {
                $result = $service->transfer($this);    
                if($result["respCode"] == "SUCCESS") {
                    $this->order_status = 1;
                    $this->save();
                } else {
                    throw new \Exception($result["errorMsg"]);
                }
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                admin_error("提现失败", $th->getMessage());
            }
        } elseif ($this->pay_type == 6) {
            $service = new DFPay();
            DB::beginTransaction();
            try {
                $result = $service->transfer($this);    
                if($result["code"] == "200") {
                    $this->order_status = 1;
                    $this->save();
                } else {
                    throw new \Exception($result["msg"]);
                }
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                admin_error("提现失败", $th->getMessage());
            }
        } elseif ($this->pay_type == 7) {
            $service = new SharkPay();
            DB::beginTransaction();
            try {
                $result = $service->transfer($this);    
                if($result["code"] == "200") {
                    $this->order_status = 1;
                    $this->save();
                } else {
                    throw new \Exception($result["msg"]);
                }
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                admin_error("提现失败", $th->getMessage());
            }
        } elseif ($this->pay_type == 8) {
            $service = new GTPay();
            DB::beginTransaction();
            try {
                $result = $service->transfer($this);    
                if($result["code"] == "0") {
                    $this->order_status = 1;
                    $this->save();
                } else {
                    throw new \Exception($result["msg"]);
                }
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                admin_error("提现失败", $th->getMessage());
            }
        } elseif ($this->pay_type == 2) {
            // 线下打款处理
            DB::beginTransaction();
            try {
                $this->order_status = 1;
                $this->pay_status = 1;
                $this->pay_time = now();
                $this->save();
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                admin_error("提现失败", $th->getMessage());
            }
        } elseif ($this->pay_type == 9) {
            $service = new PPay();
            DB::beginTransaction();
            try {
                $result = $service->transfer($this);    
                if($result["code"] == "SUCCESS") {
                    $this->order_status = 1;
                    $this->save();
                } else {
                    throw new \Exception($result["msg"]);
                }
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                admin_error("提现失败", $th->getMessage());
            }
        } elseif ($this->pay_type == 10) {
            $service = new MPay();
            DB::beginTransaction();
            try {
                $result = $service->transfer($this);    
                if($result["code"] == 0) {
                    $this->order_status = 1;
                    $this->save();
                } else {
                    throw new \Exception($result["message"]);
                }
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                admin_error("提现失败", $th->getMessage());
            }
        } elseif ($this->pay_type == 11) {
            $service = new FFPay();
            DB::beginTransaction();
            try {
                $result = $service->transfer($this);    
                if($result["respCode"] == "SUCCESS") {
                    $this->order_status = 1;
                    $this->save();
                } else {
                    throw new \Exception($result["errorMsg"]);
                }
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                admin_error("提现失败", $th->getMessage());
            }
        } elseif ($this->pay_type == 12) {
            $service = new XDPay("x1");
            DB::beginTransaction();
            try {
                $result = $service->transfer($this);    
                if($result["code"] == "200") {
                    $this->order_status = 1;
                    $this->save();
                } else {
                    throw new \Exception($result["msg"]);
                }
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                admin_error("提现失败", $th->getMessage());
            }
        } elseif ($this->pay_type == 13) {
            $service = new XDPay("dgm");
            DB::beginTransaction();
            try {
                $result = $service->transfer($this);    
                if($result["code"] == "200") {
                    $this->order_status = 1;
                    $this->save();
                } else {
                    throw new \Exception($result["msg"]);
                }
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                admin_error("提现失败", $th->getMessage());
            }
        } elseif ($this->pay_type == 14) {
            $service = new XDPay("x2");
            DB::beginTransaction();
            try {
                $result = $service->transfer($this);    
                if($result["code"] == "200") {
                    $this->order_status = 1;
                    $this->save();
                } else {
                    throw new \Exception($result["msg"]);
                }
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                admin_error("提现失败", $th->getMessage());
            }
        } elseif ($this->pay_type == 15) {
            $service = new WOWPay();
            DB::beginTransaction();
            try {
                $result = $service->transfer($this);    
                if($result["respCode"] == "SUCCESS") {
                    $this->order_status = 1;
                    $this->save();
                } else {
                    throw new \Exception($result["errorMsg"]);
                }
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                admin_error("提现失败", $th->getMessage());
            }
        } else if ($this->pay_type == 16) {
            $service = new PTMPay();
            DB::beginTransaction();
            try {
                $result = $service->transfer($this);    
                if($result["status"] == "1") {
                    $this->order_status = 1;
                    $this->save();
                } else {
                    throw new \Exception($result["msg"]);
                }
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                admin_error("提现失败", $th->getMessage());
            }
        }
    
    }

    // 审核拒绝后的操作
    public function afterRejected(){
        // 初始化提现金额
        $amount = $this->amount;
        // 获取手续费比例
        $service_fee = floatval(
            setting("WITHDRAWAL_SERVICE_FEE", 5)
        );
        // 计算补上手续费的金额
        if ($service_fee > 0) {
            if ($service_fee == 100) {
                // 100%的服务费时，没有可以退的
                return;
            }
            $amount = $amount / (1 - ($service_fee / 100));
        }

        // 退回用户余额
        $userRepository = app(UserRepository::class);
        $userRepository->addBalance([
            "user_id" => $this->user_id,
            "money" => $amount,
            "log_type" => 20
        ]);

    }

    protected static function booted(): void
    {
        // static::updating(function(UserWithdrawal $drawal){
        //     if ($drawal->status == 1 && $drawal->order_status == 2) {
        //         // 检查余额
        //         if ($drawal->user->balance < $drawal->amount) {
        //             admin_error("余额不足", "Insufficient balance");
        //             return;
        //         }
        //     }
        // });

        // static::updated(function(UserWithdrawal $drawal){

        //     if ($drawal->status == 1 && $drawal->order_status == 2) {
        //         if ($drawal->user->balance < $drawal->amount) {
        //             return;
        //         }
        //         if ($drawal->pay_type == 1) {
        //             $service = new CSPay();
        //             DB::beginTransaction();
        //             try {
        //                 $result = $service->transfer($drawal);    
        //                 if($result["status"] == "success") {
        //                     $drawal->order_status = 1;
        //                     $drawal->save();
        //                 } else {
        //                     throw new \Exception($result["msg"]);
        //                 }
        //                 DB::commit();
        //             } catch (\Throwable $th) {
        //                 DB::rollBack();
        //                 admin_error("提现失败", $th->getMessage());
        //             }
        //         } elseif ($drawal->pay_type == 3) {
        //             $service = new YTPay();
        //             DB::beginTransaction();
        //             try {
        //                 $result = $service->transfer($drawal);    
        //                 if($result["code"] == 0) {
        //                     $drawal->order_status = 1;
        //                     $drawal->save();
        //                 } else {
        //                     throw new \Exception($result["message"]);
        //                 }
        //                 DB::commit();
        //             } catch (\Throwable $th) {
        //                 DB::rollBack();
        //                 admin_error("提现失败", $th->getMessage());
        //             }
        //         }
        //     }
        // });
    }
}
