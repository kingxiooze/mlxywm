<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Payment\CSPay;
use App\Repositories\OrderRepository;
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
use Illuminate\Support\Facades\Log;

class PayNotifyController extends Controller
{
    // CSPAY支付回调
    public function postPayCspay(Request $request) {
        $service = new CSPay();
        try {
            $order_no = $service->pay_notify_verify();
        } catch (\Throwable $th) {
            return "ERROR";
        }

        // $outTradeNo = request("orderid", "0000");
        $amount = request("amount", "0");
        $transaction_id = request("transaction_id", null);
        $trade_state = request("returncode", null);

        $orderRepository = app(OrderRepository::class);
        $orderRepository->orderSuccess(
            $order_no, $amount, $transaction_id,
            $trade_state == "00"
        );

        return "OK";
    }

    // CSPAY提现回调
    public function postTransferCspay(Request $request) {
        $service = new CSPay();
        try {
            $order_no = $service->transfer_notify_verify();
        } catch (\Throwable $th) {
            return "ERROR";
        }

        $amount = request("amount", "0");
        $transaction_id = request("transaction_id", null);
        $trade_state = request("returncode", null);

        $orderRepository = app(OrderRepository::class);
        $orderRepository->withdrawalSuccess(
            $order_no, $amount, $transaction_id,
            $trade_state == "00"
        );

        return "OK";
    }

    // YTPAY支付回调
    public function postPayYtpay(Request $request) {
        $service = new YTPay();
        try {
            $order_no = $service->pay_notify_verify();
        } catch (\Throwable $th) {
            return "ERROR";
        }

        // $outTradeNo = request("orderid", "0000");
        $amount = request("amount", "0");
        $transaction_id = request("orderNo", null);
        $trade_state = request("status", null);

        $orderRepository = app(OrderRepository::class);
        $orderRepository->orderSuccess(
            $order_no, $amount, $transaction_id,
            $trade_state == "1"
        );

        return "OK";
    }

    // YTPAY提现回调
    public function postTransferYtpay(Request $request) {
        $service = new YTPay();
        try {
            $order_no = $service->transfer_notify_verify();
        } catch (\Throwable $th) {
            return "ERROR";
        }

        $amount = request("amount", "0");
        $transaction_id = request("orderNo", null);
        $trade_state = request("status", null);

        $orderRepository = app(OrderRepository::class);
        $orderRepository->withdrawalSuccess(
            $order_no, $amount, $transaction_id,
            $trade_state == "1"
        );

        return "OK";
    }

    // GSPAY支付回调
    public function postPayGspay(Request $request) {
        $service = new GSPay();
        try {
            $order_no = $service->pay_notify_verify();
        } catch (\Throwable $th) {
            return "ERROR";
        }

        $amount = request("amount", "0");
        $transaction_id = request("payOrderId", null);
        $trade_state = request("status", null);

        $orderRepository = app(OrderRepository::class);
        $orderRepository->orderSuccess(
            $order_no, $amount, $transaction_id,
            $trade_state == "2"
        );

        return "success";
    }

    // GSPAY提现回调
    public function postTransferGspay(Request $request) {
        $service = new GSPay("payment.gspay2");
        try {
            $order_no = $service->transfer_notify_verify();
        } catch (\Throwable $th) {
            return "ERROR";
        }

        $amount = request("amount", "0");
        $transaction_id = request("agentpayOrderId", null);
        $trade_state = request("status", null);

        $orderRepository = app(OrderRepository::class);
        $orderRepository->withdrawalSuccess(
            $order_no, $amount, $transaction_id,
            $trade_state == "2"
        );

        return "success";
    }

    // WEPAY支付回调
    public function postPayWepay(Request $request) {
        $service = new WEPay();
        try {
            $order_no = $service->pay_notify_verify();
        } catch (\Throwable $th) {
            return "ERROR";
        }

        $amount = request("oriAmount", "0");
        $transaction_id = request("orderNo", null);
        $trade_state = request("tradeResult", null);

        $orderRepository = app(OrderRepository::class);
        $orderRepository->orderSuccess(
            $order_no, $amount, $transaction_id,
            $trade_state == "1"
        );

        return "success";
    }

    // WEPAY提现回调
    public function postTransferWepay(Request $request) {
        $service = new WEPay();
        try {
            $order_no = $service->transfer_notify_verify();
        } catch (\Throwable $th) {
            return "ERROR";
        }

        $amount = request("transferAmount", "0");
        $transaction_id = request("tradeNo", null);
        $trade_state = request("tradeResult", null);

        $orderRepository = app(OrderRepository::class);
        $orderRepository->withdrawalSuccess(
            $order_no, $amount, $transaction_id,
            $trade_state == "1"
        );

        return "success";
    }

    // DFPAY支付回调
    public function postPayDfpay(Request $request) {
        $service = new DFPay();
        try {
            $order_no = $service->pay_notify_verify();
        } catch (\Throwable $th) {
            return "ERROR";
        }

        $amount = request("amount", "0");
        $transaction_id = request("orderId", null);
        $trade_state = request("status", null);

        $orderRepository = app(OrderRepository::class);
        $orderRepository->orderSuccess(
            $order_no, $amount, $transaction_id,
            $trade_state == "1"
        );

        return "success";
    }

    // DFPAY提现回调
    public function postTransferDfpay(Request $request) {
        $service = new DFPay();
        try {
            $order_no = $service->transfer_notify_verify();
        } catch (\Throwable $th) {
            return "ERROR";
        }

        $amount = request("amount", "0");
        $transaction_id = request("orderId", null);
        $trade_state = request("status", null);

        $orderRepository = app(OrderRepository::class);
        $orderRepository->withdrawalSuccess(
            $order_no, $amount, $transaction_id,
            $trade_state == "1"
        );

        return "success";
    }

    // SHARKPAY支付回调
    public function postPaySharkpay(Request $request) {
        $service = new SharkPay();
        try {
            //$order_no = $service->pay_notify_verify();
        } catch (\Throwable $th) {
            return "ERROR";
        }
         $Aesdata = request("data",null);
       
         $data =  $this->decryptAes($Aesdata);
        
        $order_no = $data["merchantNo"];
        $amount = $data["amount"]/100;
        $transaction_id = $data["channelNo"];
        $trade_state = $data["status"];
   
        $orderRepository = app(OrderRepository::class);
        $orderRepository->orderSuccess(
            $order_no, $amount, $transaction_id,
            $trade_state == "2"
        );

        return "success";
    }
    
    public function decryptAes($aesSecret)
    {
        $method = 'AES-128-CBC';  
        $key = "M986e61H0DOcz3KU"; 
        $str="";
        for($i=0;$i<strlen($aesSecret)-1;$i+=2){
            $str.=chr(hexdec($aesSecret[$i].$aesSecret[$i+1]));
        }
        $jsonData =  openssl_decrypt($str,$method,$key, OPENSSL_RAW_DATA,$key);
        $data = json_decode($jsonData,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        return $data;
    }
    // SHARKPAY提现回调
    public function postTransferSharkpay(Request $request) {
        $service = new SharkPay();
        
        $Aesdata = request("data",null);
        $data =  $this->decryptAes($Aesdata);

        $order_no = $data["merchantNo"];
        $amount = $data["amount"]/100;
        $transaction_id = $data["channelNo"];
        $trade_state = $data["status"];
        
        $orderRepository = app(OrderRepository::class);
        $orderRepository->withdrawalSuccess(
            $order_no, $amount, $transaction_id,
            $trade_state == "2"
        );

        return "success";
    }

    // GTPAY支付回调
    public function postPayGtpay(Request $request) {
        $service = new GTPay();
        try {
            $order_no = $service->pay_notify_verify();
        } catch (\Throwable $th) {
            return "fail";
        }

        $amount = request("money", "0");
        $transaction_id = request("order_no", null);
        $trade_state = request("status", null);

        $orderRepository = app(OrderRepository::class);
        $orderRepository->orderSuccess(
            $order_no, $amount, $transaction_id,
            $trade_state == "1"
        );

        return "SUCCESS";
    }

    // GTPAY提现回调
    public function postTransferGtpay(Request $request) {
        $service = new GTPay();
        try {
            $order_no = $service->transfer_notify_verify();
        } catch (\Throwable $th) {
            return "fail";
        }

        $amount = request("money", "0");
        $transaction_id = request("order_no", null);
        $trade_state = request("status", null);

        $orderRepository = app(OrderRepository::class);
        $orderRepository->withdrawalSuccess(
            $order_no, $amount, $transaction_id,
            $trade_state == "1"
        );

        return "SUCCESS";
    }

    // PPAY支付回调
    public function postPayPpay(Request $request) {
        $service = new PPay();
        try {
            $order_no = $service->pay_notify_verify();
        } catch (\Throwable $th) {
            return "fail";
        }

        $amount = request("amount", "0");
        $transaction_id = request("ptOrderNo", null);
        $trade_state = request("result", null);

        $orderRepository = app(OrderRepository::class);
        $orderRepository->orderSuccess(
            $order_no, $amount, $transaction_id,
            $trade_state == "1"
        );

        return "success";
    }

    // PPAY提现回调
    public function postTransferPpay(Request $request) {
        $service = new PPay();
        try {
            $order_no = $service->transfer_notify_verify();
        } catch (\Throwable $th) {
            return "fail";
        }

        $amount = request("amount", "0");
        $transaction_id = request("ptOrderNo", null);
        $trade_state = request("result", null);

        $orderRepository = app(OrderRepository::class);
        $orderRepository->withdrawalSuccess(
            $order_no, $amount, $transaction_id,
            $trade_state == "1"
        );

        return "success";
    }

    // FFPAY支付回调
    public function postPayFfpay(Request $request) {
        $service = new FFPay();
        try {
            $order_no = $service->pay_notify_verify();
        } catch (\Throwable $th) {
            return "ERROR";
        }

        $amount = request("oriAmount", "0");
        $transaction_id = request("orderNo", null);
        $trade_state = request("tradeResult", null);

        $orderRepository = app(OrderRepository::class);
        $orderRepository->orderSuccess(
            $order_no, $amount, $transaction_id,
            $trade_state == "1"
        );

        return "success";
    }

    // FFPAY提现回调
    public function postTransferFfpay(Request $request) {
        $service = new FFPay();
        try {
            $order_no = $service->transfer_notify_verify();
        } catch (\Throwable $th) {
            return "ERROR";
        }

        $amount = request("transferAmount", "0");
        $transaction_id = request("tradeNo", null);
        $trade_state = request("tradeResult", null);

        $orderRepository = app(OrderRepository::class);
        $orderRepository->withdrawalSuccess(
            $order_no, $amount, $transaction_id,
            $trade_state == "1"
        );

        return "success";
    }

    // XDPAY支付回调
    public function postPayXdpay(Request $request, string $mch) {
        $service = new XDPay($mch);
        try {
            $order_no = $service->pay_notify_verify();
        } catch (\Throwable $th) {
            return "ERROR";
        }

        $amount = request("amount", "0");
        $transaction_id = request("orderId", null);
        $trade_state = request("status", null);

        $orderRepository = app(OrderRepository::class);
        $orderRepository->orderSuccess(
            $order_no, $amount, $transaction_id,
            $trade_state == "1"
        );

        return "success";
    }

    // XDPAY提现回调
    public function postTransferXdpay(Request $request, string $mch) {
        $service = new XDPay($mch);
        try {
            $order_no = $service->transfer_notify_verify();
        } catch (\Throwable $th) {
            return "ERROR";
        }

        $amount = request("amount", "0");
        $transaction_id = request("orderId", null);
        $trade_state = request("status", null);

        $orderRepository = app(OrderRepository::class);
        $orderRepository->withdrawalSuccess(
            $order_no, $amount, $transaction_id,
            $trade_state == "1"
        );

        return "success";
    }

    // WOWPAY支付回调
    public function postPayWowpay(Request $request) {
        $service = new WOWPay();
        try {
            $order_no = $service->pay_notify_verify();
        } catch (\Throwable $th) {
            return "ERROR";
        }

        $amount = request("oriAmount", "0");
        $transaction_id = request("orderNo", null);
        $trade_state = request("tradeResult", null);

        $orderRepository = app(OrderRepository::class);
        $orderRepository->orderSuccess(
            $order_no, $amount, $transaction_id,
            $trade_state == "1"
        );

        return "success";
    }

    // WOWPAY提现回调
    public function postTransferWowpay(Request $request) {
        $service = new WOWPay();
        try {
            $order_no = $service->transfer_notify_verify();
        } catch (\Throwable $th) {
            return "ERROR";
        }

        $amount = request("transferAmount", "0");
        $transaction_id = request("tradeNo", null);
        $trade_state = request("tradeResult", null);

        $orderRepository = app(OrderRepository::class);
        $orderRepository->withdrawalSuccess(
            $order_no, $amount, $transaction_id,
            $trade_state == "1"
        );

        return "success";
    }
    
    // MPAY支付回调
    public function postPayMpay(Request $request) {
        $service = new MPay();
        try {
            $order_no = $service->pay_notify_verify();
        } catch (\Throwable $th) {
            return "ERROR";
        }

        // $outTradeNo = request("orderid", "0000");
        $amount = request("amount", "0");
        $transaction_id = request("platformOrderNo", null);
        $trade_state = request("status", null);

        $orderRepository = app(OrderRepository::class);
        $orderRepository->orderSuccess(
            $order_no, $amount, $transaction_id,
            $trade_state == "1"
        );

        return "success";
    }

    // MPAY提现回调
    public function postTransferMpay(Request $request) {
        $service = new MPay();
        try {
            $order_no = $service->transfer_notify_verify();
        } catch (\Throwable $th) {
            return "ERROR";
        }

        $amount = request("amount", "0");
        $transaction_id = request("platformOrderNo", null);
        $trade_state = request("status", null);

        $orderRepository = app(OrderRepository::class);
        $orderRepository->withdrawalSuccess(
            $order_no, $amount, $transaction_id,
            $trade_state == "1"
        );

        return "success";
    }

    // PTMPAY支付回调
    public function postPayPtmpay(Request $request) {
        $service = new PTMPay();
        try {
            $order_no = $service->pay_notify_verify();
        } catch (\Throwable $th) {
            return "ERROR";
        }

        // $outTradeNo = request("orderid", "0000");
        $amount = request("amount", "0");
        $transaction_id = request("transaction_id", null);
        $trade_state = request("returncode", null);

        $orderRepository = app(OrderRepository::class);
        $orderRepository->orderSuccess(
            $order_no, $amount, $transaction_id,
            $trade_state == "00"
        );

        return "OK";
    }

    // PTMPAY提现回调
    public function postTransferPtmpay(Request $request) {
        $service = new PTMPay();
        try {
            $order_no = $service->transfer_notify_verify();
        } catch (\Throwable $th) {
            return "ERROR";
        }

        $amount = request("amount", "0");
        $transaction_id = request("transaction_id", null);
        $trade_state = request("returncode", null);

        $orderRepository = app(OrderRepository::class);
        $orderRepository->withdrawalSuccess(
            $order_no, $amount, $transaction_id,
            $trade_state == "00"
        );

        return "OK";
    }
}
