<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Repositories\OrderRepository;

class OrderUsdt extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'order_usdt';

    protected $fillable = [
        "user_id",
        "order_id",
        "amount",
        "image",
        "status"
    ];

    public function order() {
        return $this->belongsTo(Order::class);
    }

    protected static function booted(): void
    {
        static::updated(function(OrderUsdt $usdt){
            if ($usdt->order) {
                $order = $usdt->order;
                $is_status_no_pay = (
                    $order->order_status == 1
                ) && (
                    $order->pay_status == 2
                );
                if (($usdt->status == 1) && $is_status_no_pay) {
                    $orderRepository = app(OrderRepository::class);
                    $orderRepository->orderSuccess(
                        $order->order_no, 
                        $usdt->amount, 
                        $order->order_no,
                        true
                    );
                }
            }

        });
    }
    
}
