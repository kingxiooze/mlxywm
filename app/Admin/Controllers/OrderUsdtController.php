<?php

namespace App\Admin\Controllers;

use App\Models\OrderUsdt;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class OrderUsdtController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new OrderUsdt(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('user_id');
            $grid->column('order_id');
            $grid->column('amount');
            $grid->column('image');
            $grid->column('status');
            // $grid->column('created_at');
            // $grid->column('updated_at')->sortable();
            $grid->model()->orderBy('id', 'desc');
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
        return Show::make($id, new OrderUsdt(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('order_id');
            $show->field('amount');
            $show->field('image');
            $show->field('status');
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
        return Form::make(new OrderUsdt(), function (Form $form) {
            $form->text('user_id')->readOnly();
            // $form->text('order_id', "订单ID")->readOnly();
            $form->text('amount')->readOnly();
            $form->display('image')->with(function ($value){
                return "<img src='$value' style='width:-webkit-fill-available;' />";
            });

            if ($form->model()->status == 0) {
                $form->radio('status')->options([
                    "0" => "待审核",
                    "1" => "审核成功",
                    "2" => "审核失败",
                ]);
            } else {
                $form->radio('status')->options([
                    "0" => "待审核",
                    "1" => "审核成功",
                    "2" => "审核失败",
                ])->disable();
                $form->disableSubmitButton();
                $form->disableResetButton();
                
            }
            $form->disableDeleteButton();
            $form->disableListButton();
            $form->disableViewButton();
            
            $form->saved(function (Form $form) {
                return $form->response()->success('保存成功')->redirect('order');
            });
        });
    }
}
