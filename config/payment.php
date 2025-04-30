<?php

return [
    "cspay" => [
        "mid" => env('CSPAY_MID'),
        "apikey" => env('CSPAY_API_KEY'),
    ],

    "ytpay" => [
        "mid" => env('YTPAY_MID'),
        "apikey" => env('YTPAY_API_KEY'),
    ],

    // 代收
    "gspay" => [
        "mid" => env('GSPAY_MID'),
        "appid" => env('GSPAY_APP_ID'),
        "key" => env('GSPAY_KEY'),
    ],
    // 代付
    "gspay2" => [
        "mid" => env('GSPAY2_MID'),
        "appid" => env('GSPAY2_APP_ID'),
        "key" => env('GSPAY2_KEY'),
    ],
    "wepay" => [
        "mid" => env('WEPAY_MID'),
        // 支付(代收)密钥
        "pay_key" => env('WEPAY_PAY_KEY'),
        // 提现（代付）密钥
        "transfer_key" => env('WEPAY_TRANSFER_KEY')
    ],
    "dfpay" => [
        "mid" => env('DFPAY_MID'),
        // 支付编码
        "pay_code" => env('DFPAY_PAY_CODE'),
        "key" => env("DFPAY_KEY")
    ],
    "sharkpay" => [
        "mid" => env('SHARKPAY_MID'),
        // 支付编码
        "pay_code" => env('SHARKPAY_PAY_CODE'),
        "key" => env("SHARKPAY_KEY")
    ],
    "gtpay" => [
        // 商户号
        "mid" => env('GTPAY_MID'),
        // API密钥
        "apikey" => env('GTPAY_API_KEY'),
    ],
    "ppay" => [
        // 商户号
        "mid" => env('PPAY_MID'),
        // 接口密钥
        "apikey" => env('PPAY_API_KEY'),
    ],
    "mpay" => [
        "mid" => env('MPAY_MID'),
        "apikey" => env('MPAY_API_KEY'),
    ],

    "ffpay" => [
        "mid" => env('FFPAY_MID'),
        // 支付(代收)密钥
        "pay_key" => env('FFPAY_PAY_KEY'),
        // 提现（代付）密钥
        "transfer_key" => env('FFPAY_TRANSFER_KEY')
    ],
    "xdpay-x1" => [
        "mid" => env('XDPAY_X1_MID'),
        // 代收支付编码
        "pay_pay_code" => env('XDPAY_X1_PAY_PAY_CODE'),
        // 代付支付编码
        "transfer_pay_code" => env('XDPAY_X1_TRANSFER_PAY_CODE'),
        "key" => env("XDPAY_X1_KEY")
    ],
    "xdpay-dgm" => [
        "mid" => env('XDPAY_DGM_MID'),
        // 代收支付编码
        "pay_pay_code" => env('XDPAY_DGM_PAY_PAY_CODE'),
        // 代付支付编码
        "transfer_pay_code" => env('XDPAY_DGM_TRANSFER_PAY_CODE'),
        "key" => env("XDPAY_DGM_KEY")
    ],
    "xdpay-x2" => [
        "mid" => env('XDPAY_X2_MID'),
        // 代收支付编码
        "pay_pay_code" => env('XDPAY_X2_PAY_PAY_CODE'),
        // 代付支付编码
        "transfer_pay_code" => env('XDPAY_X2_TRANSFER_PAY_CODE'),
        "key" => env("XDPAY_X2_KEY")
    ],
    "wowpay" => [
        "mid" => env('WOWPAY_MID'),
        // 支付(代收)密钥
        "pay_key" => env('WOWPAY_PAY_KEY'),
        // 提现（代付）密钥
        "transfer_key" => env('WOWPAY_TRANSFER_KEY')
    ],
    "ptmpay" => [
        "mid" => env('PTMPAY_MID'),
        "apikey" => env('PTMPAY_API_KEY'),
    ],
];