<?php

namespace App\Admin\Controllers;

use App\Models\UserBankcard;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class UserBankcardController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new UserBankcard(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('user_id');
            $grid->column('bank_name');
            $grid->column('card_no');
            $grid->column('name');
            $grid->column('mobile');
            $grid->column('ifsc_code');
            // $grid->column('created_at');
            // $grid->column('updated_at')->sortable();
        
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
        return Show::make($id, new UserBankcard(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('bank_name');
            $show->field('card_no');
            $show->field('name');
            $show->field('mobile');
            $show->field('ifsc_code');
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
        return Form::make(new UserBankcard(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->text('bank_name');
            $form->text('card_no');
            $form->text('name');
            $form->text('mobile');
            $form->text('ifsc_code');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
