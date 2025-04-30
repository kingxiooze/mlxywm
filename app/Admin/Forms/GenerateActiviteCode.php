<?php

namespace App\Admin\Forms;

use App\Models\UserActiviteCode;
use Dcat\Admin\Widgets\Form;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Illuminate\Support\Str;

class GenerateActiviteCode extends Form implements LazyRenderable
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
        for ($i=0; $i < $input["num"]; $i++) { 
            UserActiviteCode::create([
                // "user_id" => $input["user_id"],
                "code" => Str::random(6)
            ]);
        }

        return $this
				->response()
				->success('生成成功')
				->refresh();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        // $this->text('user_id', "所属用户ID")->required();
        $this->number('num', "生成数量")->required();
    }

}
