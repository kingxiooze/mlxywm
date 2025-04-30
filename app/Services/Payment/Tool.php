<?php

namespace App\Services\Payment;

// 支付工具类
class Tool
{
    //生成订单号/退款单号 O_订单号开头 R_退款单号开头
    public static function generateOutTradeNo($typeName = "", $length = 6)
    {
        $rStr = rand(pow(10, ($length - 1)), pow(10, $length) - 1);
        return $typeName . date('YmdHis') . $rStr;
    }

}