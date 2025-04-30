<?php

namespace App\Admin\Forms;

use Dcat\Admin\Widgets\Form;
//use App\Repositories\UserRepository;
use App\Models\TaskIndex;
use App\Models\TaskModel;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Contracts\LazyRenderable;
use Illuminate\Support\Arr;
use Carbon\Carbon;
class CopyTaskOrder extends Form implements LazyRenderable
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
        $TaskModels = TaskModel::where("task_index_id",$this->payload["id"])->get();//find($this->payload["id"]);
        try {
           //创建index
           $TaskOrder= TaskIndex::create([
                    "name" => $input["name"]]
                   ); 
            foreach ($TaskModels as $TaskModel) {
            $newTaskModel= TaskModel::create([
                    "number" => $TaskModel->number,
                    "type" => $TaskModel->type,
                    "cmtype" => $TaskModel->cmtype,
                    "commission" => $TaskModel->commission,
                    "task_index_id" => $TaskModel->task_index_id,
                    "item_price" => $TaskModel->item_price
                   ]); 
            }
            
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
        //$this->hidden("balance_type")->value("balance");
        $this->text('name', "分组名称")
            
            ->help("复制分组下所有任务")
            ->required();
        //$this->hidden("user_id");
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
