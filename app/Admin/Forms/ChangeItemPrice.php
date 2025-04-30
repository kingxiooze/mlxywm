<?php

namespace App\Admin\Forms;

use Dcat\Admin\Widgets\Form;
use App\Models\Item;
use App\Models\ItemPriceAuditLog;

class ChangeItemPrice extends Form
{
    /**
     * Handle the form request.
     *
     * @param array $input
     *
     * @return mixed
     */
    public function handle(array $input)
    {

        try {
            $item = Item::find($input["item_id"]);

            $before_price = $item->price;

            $item->price += $input["change_amount"];

            $after_price = $item->price;

            $item->save();

            ItemPriceAuditLog::create([
                "item_id" => $item->id,
                "before_price" => $before_price,
                "after_price" => $after_price,
                "amount" => $input["change_amount"],
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
        $this->number('change_amount', "增减额度")
            // ->rules("numeric")
            ->help("复数表示扣除价格，正数表示增添价格")
            ->required();
        $this->hidden("item_id");
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
