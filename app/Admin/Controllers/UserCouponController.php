<?php

namespace App\Admin\Controllers;

use App\Models\UserCoupon;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class UserCouponController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new UserCoupon(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('user_id');
            $grid->column('coupon_id');
            $grid->column('status');
            $grid->column('item_id');
            $grid->column('expire_at');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();
        
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
        return Show::make($id, new UserCoupon(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('coupon_id');
            $show->field('status');
            $show->field('item_id');
            $show->field('expire_at');
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
        return Form::make(new UserCoupon(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->text('coupon_id');
            $form->text('status');
            $form->text('item_id');
            $form->text('expire_at');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
