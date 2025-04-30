<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Grid\BatchCreateWhitelist;
use App\Models\TaskModel;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Http\Request;

class TaskModelController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $model = new TaskModel();
       
        return Grid::make($model, function (Grid $grid) {
            $grid->column('id')->sortable();
            // $grid->column('user_id');
            $grid->column('number', "编号");
            // $grid->column('item_id');
            $grid->column('type', "是否爆单")->switch();
            $grid->column('cmtype', "是否固定佣金")->switch();
            $grid->column('commission', "佣金");
            $grid->column('item_price', "商品价格(比例0.99)");
            $grid->model()->orderBy("number", "asc");
            $grid->tools(function ($tools) {
            $taskIndexId = request()->get('task_index_id');
            $url = admin_url('taskmodel/create?task_index_id=' . $taskIndexId);
            $tools->append("<a class='btn btn-primary' href='{$url}'>新增</a>");
            });
            $grid->disableCreateButton(); // 关闭默认的新增按钮
            $grid->filter(function (Grid\Filter $filter) {
                 $filter->equal('task_index_id');
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
        return Show::make($id, new TaskModel(), function (Show $show) {
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
        return Form::make(new TaskModel(), function (Form $form) {
            $form->display('id');
            $form->text('number');
            $form->switch('type',"是否爆单");
            $form->text('commission',"佣金(比例)");
            $form->text('item_price',"商品价格(比例0.99)");    
            $form->hidden('task_index_id')-> default(request()->get('task_index_id'));
            // $form->display('created_at');
            // $form->display('updated_at');
        });
    }
}
