<?php

namespace App\Admin\Actions\Grid;

use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Models\UserWithdrawal;

class WithdrawalTransfer extends RowAction
{
    /**
     * Handle the action request.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        $id = $this->getKey();
        $drawal = UserWithdrawal::where("id", $id)->first();
        if ($drawal) {
            $pay_type = $request->get("pay_type", null);
            if ($pay_type) {
                $drawal->pay_type = $pay_type;
                // $drawal->save();
            }

            // 如果是USDT打款则跳转到创建回执页面
            if ($pay_type == 2) {
                return $this->response()
                    ->redirect(
                        '/withdrawals_usdt_receipt/create?withdrawal_no=' . $drawal->withdrawal_no
                    );
            }
            
            $drawal->online_transfer();
        } else {
            $this->response()->error("数据不存在");
        }
        return $this->response()
            ->success('操作成功')
            ->redirect('/user_withdrawal');
    }

    /**
	 * @return string|array|void
	 */
	public function confirm()
	{
		// return ['Confirm?', 'contents'];
	}

    /**
     * @param Model|Authenticatable|HasPermissions|null $user
     *
     * @return bool
     */
    protected function authorize($user): bool
    {
        return true;
    }

    /**
     * @return array
     */
    protected function parameters()
    {
        if ($this->title == "线上打款2") {
            // 线上打款2 = WEPAY
            $pay_type = 5;
        } else if ($this->title == "线上打款1") {
            // 线上打款1 = GSPAY
            $pay_type = 4;
        } else if ($this->title == "线上打款3") {
            // 线上打款3 = DFPAY
            $pay_type = 6;
        } else if ($this->title == "线上打款-SHARKPAY") {
            // 线上打款4 = SharkPAY
            $pay_type = 7;
        } else if ($this->title == "线上打款5") {
            // 线上打款5 = GTPAY
            $pay_type = 8;
        } else if ($this->title == "线下打款-USDT") {
            // 线上打款USDT = USDT
            $pay_type = 2;
        } else if ($this->title == "线上打款-PPAY") {
            // 线上打款6 = PPAY
            $pay_type = 9;
        } else if ($this->title == "线上打款-MPAY") {
            // 线上打款-MPAY
            $pay_type = 10;
        } else if ($this->title == "线上打款-FFPay") {
            // 线上打款-FFPay
            $pay_type = 11;
        } else if ($this->title == "线上打款-XDPAY-X1") {
            // 线上打款-XDPAY-X1
            $pay_type = 12;
        } else if ($this->title == "线上打款-XDPAY-DGM") {
            // 线上打款-XDPAY-DGM
            $pay_type = 13;
        } else if ($this->title == "线上打款-XDPAY-X2") {
            // 线上打款-XDPAY-X2
            $pay_type = 14;
        } else if ($this->title == "线上打款-WOWPAY") {
            // 线上打款-WOWPAY
            $pay_type = 15;
        } else if ($this->title == "线上打款-PTMPAY") {
            // 线上打款-PTMPAY
            $pay_type = 16;
        } else {
            $pay_type = 0;
        }
        return [
            "pay_type" => $pay_type
        ];
    }
}
