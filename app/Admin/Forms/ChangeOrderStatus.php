<?php

namespace App\Admin\Forms;

use Dcat\Admin\Widgets\Form;
//use App\Repositories\UserRepository;
use App\Models\TaskOrder;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Contracts\LazyRenderable;
use Illuminate\Support\Arr;
use Carbon\Carbon;
class ChangeOrderStatus extends Form implements LazyRenderable
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
        $TaskOrder = TaskOrder::find($this->payload["id"]);
        try {
           $TaskOrder->status = 0;
           $TaskOrder->freeze_at = Carbon::now()->addHours($input["amount"]);
           $TaskOrder->save();
        } catch (\Throwable $th) {
            return $this->response()->error($th->getMessage());
        }
        

        return $this
				->response()
				->success('修改成功')
				->refresh();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        // $this->select("balance_type", "余额类型")->options([
        //     "balance" => "可提现余额",
        //     "redpacket_balance" => "红包金",
        //     "mission_balance" => "任务金",
        //     "unable_withdrawal_balance" => "无法提现余额",
        // ]);
        $this->hidden("balance_type")->value("balance");
        $this->number('amount', "延长时间")
            ->rules("numeric")
            ->help("根据当前时间往后延长(单位小时)")
            ->required();
        $this->hidden("user_id");
    }

    /**
     * The data of the form.
     *
     * @return array
     */
    public function default()
    {
        return [
            // 'name'  => 'John Doe',
            // 'email' => 'John.Doe@gmail.com',
        ];
    }
}
