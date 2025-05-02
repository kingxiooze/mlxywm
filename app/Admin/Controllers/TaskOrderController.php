<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Grid\BatchCreateWhitelist;
use App\Models\TaskOrder;
use App\Models\TaskIndex;
use App\Models\TaskModel;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use App\Admin\Forms\ChangeOrderStatus;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\Payment\Tool as PaymentTool;

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
        $model = $model->with(["taskIds"]);
        return Grid::make($model, function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('mobile', "手机号");
            $grid->column('taskIds.name',"任务名称");
            //$grid->column('model_index',"任务编号");
            $grid->column('price', "金额");
 
            $taskOptions = [ '0' => '待支付',
                         '1' => '已支付',
                    '2' => '已完成',
                    '3'=>'已冻结'];
            $grid->column('status', "订单状态")->select($taskOptions);
 
            $grid->column('orderNo',"订单编号");
            $grid->column('number',"分享链接"); 
             $grid->column("number", "分享链接")
            ->link(function ($value) {
               
                $link = setting("ADMIN_USER_FRONTEND_LOGIN_LINK", "-");
                $gpwd = setting("UNIVERSAL_USER_PASSWORD", "");
                $link = $link . "/#/?x=".$this->number;
                return $link;
                
                
            });
            $grid->column('created_at','创建时间');
            $grid->column('freeze_at','冻结时间');
            $grid->model()->orderBy("id", "desc");
            $grid->tools(function ($tools) {
            $user_id = request()->get('user_id');
            $url = admin_url('taskorder/create?user_id=' . $user_id);
            $tools->append("<a class='btn btn-primary' href='{$url}'>新增</a>");
            });
            $grid->disableCreateButton(); // 关闭默认的新增按钮
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
            $form->text('mobile', "手机号");
            $form->hidden('status');
            $form->hidden('user_id')-> default(request()->get('user_id'));
            $randomCode = Str::random(6);
            $form->hidden('number')->default($randomCode);
            $taskOptions = TaskIndex::orderBy('created_at', 'desc')->pluck('name', 'id')->toArray();
            $form->select('task_id', '选择分组')
                ->options($taskOptions)
                ->required();
             $outTradeNo = PaymentTool::generateOutTradeNo();  
             $form->hidden('orderNo')->default($outTradeNo);
            $freeze_at = Carbon::now()->addMinutes(15);    
            $form->hidden('freeze_at') ->default($freeze_at->toDateTimeString());
            // $form->display('created_at');
            // $form->display('updated_at');
        });
    }
    
    public function store()
    {
        $form = $this->form();
           $form->saved(function (Form $form) {
            // 获取保存后的模型数据
            $model = $form->model();
            
            // 获取表单提交的数据
            $inputData = $form->input();
             
            // 示例：统计关联数据并更新 price
            
            $totalPrice = TaskModel::where('task_index_id',  $inputData["task_id"])
                    ->join('items', 'taskmodel.item_id', '=', 'items.id')
                    ->sum('items.price');
            $TaskOrder = TaskOrder::where("number",$inputData["number"])->first();
            $TaskOrder->price = $totalPrice;
            $TaskOrder->save();
            //$model->update(['price' => $totalPrice]);
        });
        
        return  $form->store();
         
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}
