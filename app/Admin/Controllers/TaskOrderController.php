<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Grid\BatchCreateWhitelist;
use App\Models\TaskOrder;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use App\Admin\Forms\ChangeOrderStatus;

class TaskOrderController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $model = new TaskOrder();
        $model = $model->with(["user","taskId"]);
        return Grid::make($model, function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('user.mobile', "手机号");
            $grid->column('taskId.name',"任务名称");
            $grid->column('model_index',"任务编号");
            $grid->column('price', "金额");
           $grid->column('freeze')->display("延长冻结时间")->modal('延长冻结时间', function ($modal) {
            $id = $this->getKey(); // 使用 getKey() 而不是直接访问 id
            return ChangeOrderStatus::make()->payload(['id' => $id]);
        });
        $taskOptions = [ '0' => '待支付',
                     '1' => '已支付',
                '2' => '已完成',
                '3'=>'已冻结'];
        $grid->column('status', "订单状态")->select($taskOptions);
            // $grid->column('status')->display(function($v) {
            //     $statusMap = [
            //         0 => '待支付',
            //         1 => '已支付',
            //         2 => '已完成',
            //         3 => '已冻结'
            //     ];
            //     return $statusMap[$v] ?? '未知状态';
            // })->modal('延长冻结时间', ChangeOrderStatus::make()->payload(['id' => $this->id]));
            
             
            
            $grid->column('created_at','创建时间');
            $grid->column('freeze_at','冻结时间');
            $grid->model()->orderBy("id", "desc");

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->equal('user_id');
            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new TaskOrder(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('item_id');
            $show->field('amount');
            $show->field('created_at');
            $show->field('updated_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new TaskOrder(), function (Form $form) {
            $form->display('id');
            $form->text('status');
            
        
            // $form->display('created_at');
            // $form->display('updated_at');
        });
    }
}
