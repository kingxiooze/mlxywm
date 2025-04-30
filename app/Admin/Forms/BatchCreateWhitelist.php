<?php

namespace App\Admin\Forms;

use App\Models\User;
use App\Models\UserActiviteCode;
use App\Models\Whitelist;
use Dcat\Admin\Widgets\Form;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Illuminate\Support\Str;

class BatchCreateWhitelist extends Form implements LazyRenderable
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
        $mobiles = explode(",", $input["mobile"]);
        foreach ($mobiles as $mobile) {
            $user = User::where("mobile", $mobile)->first();
            if (empty($user)) {
                continue;
            }
            Whitelist::create([
                "user_id" => $user->id,
                "item_id" => $input["item_id"],
                "amount" => $input["amount"]
            ]);
        }

        return $this
				->response()
				->success('创建成功')
				->refresh();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->textarea('mobile', "用户手机号")->required();
        $this->text('item_id', "商品ID")->required();
        $this->text('amount', "白名单数")->required();
    }

}
