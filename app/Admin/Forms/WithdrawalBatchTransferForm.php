<?php

namespace App\Admin\Forms;

use App\Models\PayChannel;
use App\Models\UserWithdrawal;
use Dcat\Admin\Widgets\Form;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Contracts\LazyRenderable;

class WithdrawalBatchTransferForm extends Form implements LazyRenderable
{
    use LazyWidget;
    /**
     * Handle the form request.
     *
     * @param array $input
     *
     * @return mixed
     */
    public function handle(array $input)
    {
        $ids = explode(',', $input['id'] ?? null);
        $pay_type = $input['pay_type'] ?? null;
        if (! $ids) {
            return $this->response()->error('参数错误');
        }
        if ($pay_type == 2) {
            return $this->response()->error('Not Support');
        }
        $withdrawals = UserWithdrawal::whereIn("id", $ids)
            ->get();

        foreach ($withdrawals as $withdrawal) {
            $withdrawal->pay_type = $pay_type;
            $withdrawal->online_transfer();
        }

        return $this
				->response()
				->success('处理成功')
				->refresh();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->select('pay_type', "提现渠道")->options(
            PayChannel::all()->pluck("name", "pay_type")
        );
        $this->hidden('id')->attribute('id', 'withdrawal_ids');
    }

    /**
     * The data of the form.
     *
     * @return array
     */
    public function default()
    {
        return [
        ];
    }
}
