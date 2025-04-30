<?php

namespace App\Admin\Controllers;

use App\Models\RedpacketsV2;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class RedpacketsV2Controller extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $model = new RedpacketsV2();
        $model = $model->with(["salesman"]);
        return Grid::make($model, function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('salesman_mobile');
            $grid->column('remark');
            $grid->column('amount');
            $grid->column('is_vip',"是否为会员可领")->bool();
            $grid->column('count');
            $grid->column('is_sale')->bool();
            $grid->column('status')->using([0 => '已发完', 1 => '未发完']);
            // $grid->column('created_at');
            // $grid->column('updated_at')->sortable();
            
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
        return Show::make($id, new RedpacketsV2(), function (Show $show) {
            $show->field('id');
            $show->field('salesman_code');
            $show->field('remark');
            $show->field('amount');
            $show->field('count');
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
        return Form::make(new RedpacketsV2(), function (Form $form) {
            $form->display('id');
            $form->text('salesman_mobile', "业务员手机号");
            $form->text('remark');
            $form->text('amount');
            $form->number('count');
            $form->switch('is_vip',"仅会员可领");
            $form->switch('is_sale');
            $form->radio('status')
                ->options([0 => '已发完', 1 => '未发完'])
                ->default(1);
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
