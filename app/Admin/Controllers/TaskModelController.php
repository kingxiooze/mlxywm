<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Grid\BatchCreateWhitelist;
use App\Models\TaskModel;
use App\Models\Item;
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
       $model = $model->with("itemIdinfo");
        return Grid::make($model, function (Grid $grid) {
            $grid->column('id')->sortable();
            // $grid->column('user_id');
            $grid->column('number', "编号");
            $taskOptions = Item::orderBy('created_at', 'desc')->pluck('location', 'id')->toArray();
            $grid->column('item_id', "商品")->select($taskOptions);
            $grid->column('itemIdinfo.price', "价格");
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
              $taskOptions = Item::orderBy('created_at', 'desc')->pluck('location', 'id')->toArray();
            $form->select('item_id', '选择商品')
                ->options($taskOptions)
                ->required();
            //$form->text('item_id',"商品id");  
            
            $form->hidden('task_index_id')-> default(request()->get('task_index_id'));
            
            // $form->display('created_at');
            // $form->display('updated_at');
        });
    }
}
