<?php

namespace App\Admin\Controllers;

use App\Models\PrizeWheelReward;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class PrizeWheelRewardController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new PrizeWheelReward(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('name');
            $grid->column('reward_type')->using([1 => "优惠券", 2=> "产品", 3=> "现金"]);
            // $grid->column('coupon_id');
            // $grid->column('item_id');
            // $grid->column('cash_amount');
            $grid->column('rate');
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
        return Show::make($id, new PrizeWheelReward(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('reward_type');
            $show->field('coupon_id');
            $show->field('item_id');
            $show->field('cash_amount');
            $show->field('rate');
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
        return Form::make(new PrizeWheelReward(), function (Form $form) {
            $form->display('id');
            $form->text('name');
            $form->radio('reward_type')
                ->when(1, function (Form $form) {
                    $form->text('coupon_id');
                })
                ->when(2, function (Form $form) {
                    $form->text('item_id');

                })
                ->when(3, function (Form $form) {
                    $form->text('cash_amount');
                })
                ->options([
                    1 => '优惠券',
                    2 => '产品',
                    3 => '现金'
                ])
                ->default(1);
            
            $form->text('rate');
        });
    }
}
