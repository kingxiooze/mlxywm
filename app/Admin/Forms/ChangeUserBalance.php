<?php

namespace App\Admin\Forms;

use Dcat\Admin\Widgets\Form;
use App\Repositories\UserRepository;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Contracts\LazyRenderable;
use Illuminate\Support\Arr;

class ChangeUserBalance extends Form implements LazyRenderable
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
        $userRepository = app(UserRepository::class);
        try {
            $userRepository->addBalance([
                "user_id" => $input["user_id"] ?? $this->payload["user_id"],
                "money" => $input["amount"],
                "log_type" => 14,
                "balance_type" => $input["balance_type"],
            ]);
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
        $this->text('amount', "变动数量")
            ->rules("numeric")
            ->help("复数表示扣除余额，正数表示增添余额")
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
