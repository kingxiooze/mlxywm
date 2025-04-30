<?php

namespace App\Admin\Controllers;

use App\Models\UserItem;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class UserItemController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $model = new UserItem();
        $model = $model->with(["item"]);
        return Grid::make($model, function (Grid $grid) {
            $grid->column('id')->sortable();
            // $grid->column('user_id');
            $grid->column('item.name', "商品名称");
            // $grid->column('earning_end_at');
            $grid->column('serial_number', "编号");
            // $grid->column('last_earning_at', "上次收益时间");
            $grid->column('created_at', "购买时间");
            $grid->column('order_sn', "订单编号");
            $grid->column('is_stoped', "是否暂停收益")->switch();

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
        return Show::make($id, new UserItem(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('item_id');
            $show->field('earning_end_at');
            $show->field('serial_number');
            $show->field('last_earning_at');
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
        return Form::make(new UserItem(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->text('item_id');
            $form->text('earning_end_at');
            $form->text('serial_number');
            $form->text('last_earning_at');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
