<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Grid\BatchCreateWhitelist;
use App\Models\TaskIndex;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Dcat\Admin\Form\NestedForm;
use App\Admin\Forms\CopyTaskOrder;
class TaskIndexController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $model = new TaskIndex();
        return Grid::make($model, function (Grid $grid) {
            $grid->column('id')->sortable();
            // $grid->column('user_id');
            $grid->column('name', "名称");
             $grid->column("buy_log", "任务列表")->display(function(){
                return "任务列表";
            })
            ->link(function ($value) {
                return admin_url('taskmodel?task_index_id='.$this->id);
            });
            
             $grid->column('copy')->display("复制分组")->modal('复制分组', function ($modal) {
            $id = $this->getKey(); // 使用 getKey() 而不是直接访问 id
            return CopyTaskOrder::make()->payload(['id' => $id]);
        });
            
            
            $grid->model()->orderBy("id", "desc");

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
        
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
        return Show::make($id, new TaskIndex(), function (Show $show) {
            $show->field('id');
            $show->field('name');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
         return Form::make(new TaskIndex(), function (Form $form) {
        $form->text('name', '任务组标题')->required();
        
        $form->table('taskmodels', '具体任务', function (NestedForm $table) {
            $table->text('number', '编号')->rules('required|max:20');
            $table->switch("type",'是否爆单');
             $table->switch("cmtype",'是否固定');
            $table->text('item_price', '商品价格(比例0.99)');
            $table->text('commission', '佣金');
        });
    });
    }
}
