<?php

namespace App\SalesAdmin\Controllers;

use App\Admin\Actions\Grid\BatchCreateWhitelist;
use App\Models\TaskIndex;
use App\Models\TaskModel;
use App\Models\Item;
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
             $grid->column("buy_log", "商品列表")->display(function(){
                return "商品列表";
            })
            ->link(function ($value) {
                return admin_url('taskmodel?task_index_id='.$this->id);
            });
              $grid->column("total_yj", "商品总价")->display(function(){
                   //TaskModel::
                //   $totalPrice = TaskModel::where('task_index_id', $this->id)
                //     ->withSum('itemIdinfo', 'price')
                //     ->get()
                //     ->sum('item_sum_price');
                    $totalPrice = TaskModel::where('task_index_id',  $this->id)
                    ->join('items', 'taskmodel.item_id', '=', 'items.id')
                    ->sum('items.price');
                    return $totalPrice;
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
           $taskOptions = Item::orderBy('created_at', 'desc')->pluck('location', 'id')->toArray();
            $table->select('item_id', '选择商品')
                ->options($taskOptions)
                ->required();
            // $grid->column('task_id', "任务组")->select($taskOptions); 
        });
    });
    }
}
