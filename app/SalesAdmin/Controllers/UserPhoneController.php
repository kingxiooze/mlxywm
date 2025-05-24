<?php

namespace App\SalesAdmin\Controllers;
use Illuminate\Validation\ValidationException;
use App\Admin\Actions\Grid\BatchCreateWhitelist;
use App\Models\UserPhone;
use App\Models\UserSfen;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Dcat\Admin\Form\NestedForm;
use App\Admin\Forms\CopyTaskOrder;
class UserPhoneController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $model = new UserPhone();
        return Grid::make($model, function (Grid $grid) {
            $grid->column('id')->sortable();
            // $grid->column('user_id');
            $grid->column('numbers', "工号");
            $grid->column('phone', "手机号");
            $grid->column('remark', "备注");
            $taskOptions = UserSfen::orderBy('created_at', 'desc')->pluck('name', 'id')->toArray();
            $grid->column('sfen_id', "打粉人")->select($taskOptions);
            $grid->tools(function ($tools) {
            $user_id = request()->get('numbers');
            $url = admin_url('filterphone/create?numbers=' . $user_id);
            $tools->append("<a class='btn btn-primary' href='{$url}'>新增</a>");
            });
            $grid->disableCreateButton(); // 关闭默认的新增按钮
            
            $grid->model()->orderBy("id", "desc");

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->equal('numbers',"工号");
                $filter->equal('phone',"手机号");
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
        return Show::make($id, new UserPhone(), function (Show $show) {
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
        return Form::make(new UserPhone(), function (Form $form) {
        $form->hidden('numbers','工号')-> default(Admin::user()->id);
        $form->text('phone', '手机号')->rules("numeric")->required()->attribute(['type' => 'number', 'pattern' => '\d*']);
        $form->editor('remark', '备注');
       
        // $taskOptions = UserSfen::orderBy('created_at', 'desc')->pluck('name', 'id')->toArray();
        // $form->select('sfen_id', '选择打粉人')
        //         ->options($taskOptions)
        //         ->required(); 
    });
    }
    
     public function store()
    {
        $form = $this->form();
         $form->saving(function (Form $form) {
        $inputData = $form->input();
        
        // 检查手机号是否已存在
        if (UserPhone::where('phone', $inputData['phone'])->exists()) {
            // 返回错误信息并阻止保存
           throw ValidationException::withMessages([
            'phone' => '手机号已存在'
        ]);
        }
        });
        
        return  $form->store();
         
    }
    
}
