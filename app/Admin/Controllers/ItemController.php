<?php

namespace App\Admin\Controllers;

use App\Models\Item;
use App\Models\ItemCategory;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use App\Admin\Repositories\Item as ItemAdminRepository;
use App\Admin\Actions\Form\ChangeItemPrice;

class ItemController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $model = new Item();
        $model = $model->with(["category"]);
        return Grid::make($model, function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('category.name', "分类名称");
            $grid->column('name');
            $grid->column('image')->image('', 50, 50);
            $grid->column('secondary_image')->image('', 50, 50);
            $grid->column('price');
            $grid->column('purchase_limit');
            // $grid->column('cashback',"返现1次");
            // $grid->column('cashback_two',"返现2次");
            // $grid->column('cashback_three',"返现3次");
            // $grid->column('gain_per_day', "日收益金额");
            // $grid->column('gain_day_num', "收益天数");
            // $grid->column('cashback');
            // $grid->column('created_at');
            // $grid->column('updated_at')->sortable();
            $grid->column('sort', "排序")->sortable();
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
        return Show::make($id, new Item(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('image');
            $show->field('price');
            $show->field('gain_per_day');
            $show->field('gain_day_num');
            $show->field('cashback');
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
        return Form::make(new ItemAdminRepository(), function (Form $form) {
           $form->display('id');
            $form->text('name',"英文名称");
            $form->text("location", "中文名称");
            $form->select("category_id", "选择店铺")->options(
                ItemCategory::query()->pluck("cnname", "id")
            );
            $form->image('image')->autoUpload()->saveFullUrl();
            //$form->image('secondary_image')->autoUpload()->saveFullUrl();
            $form->editor("description", "描述");
            
            //$form->editor("characteristic", "商品特点");

            if ($form->isEditing()) {
                $form->text('price')->readOnly();
                $balanceTool = new ChangeItemPrice();
                $balanceTool->setKey($form->model()->id);
                $form->html($balanceTool, "修改价格");
            } else {
                $form->text('price');
            }
            

            // $form->number("purchase_limit");
            // $form->text('gain_per_day', "日收益金额");
            // $form->text('gain_day_num', "收益天数");
            // $form->text('cashback', "返现1次");
            // $form->text('cashback_two', "返现2次");
            // $form->text('cashback_three', "返现3次");
            // $form->number('stock');
            // $form->number('up_item');
            // $form->switch("is_group_purchase");
            // $form->text("group_people_count", "拼团需求人数");
            // $form->text("logistics_hours");
            // $form->datetimeRange("gp_start_time", "gp_end_time", '团购开始结束时间');
            // $form->text("group_purchase_end_hours");
            // $form->text("group_purchase_end_hours");
            // $form->text("group_purchase_end_hours");
            // $form->switch("is_sell");
            // $form->number("joined_count_display", "可控参与人数");
            // $form->number("remain_sales_amount", "剩余售卖额度");

            // $form->number("auto_dec_stock", "自动扣除库存数量");
            // $form->number("sort", "排序");
            // $form->switch("is_earning_at_end", "是否到期后领取所有收益");
            // $form->switch("is_cash_xz", "是否需要限制上级商品返现");
            // // 20230914: amd的商品购买受发售时间控制功能迁移过来
            // $form->datetimeRange("presale_start_at", "presale_end_at", '预购开始结束时间');
            // 20230926: 商品增加白名单开关
            // 如果开了白名单就必须是白名单的用户才能购买，
            // 且购买数量受白名单数量字段限制
            //$form->switch("whitelist_status", "白名单开关");
        });
    }
}
