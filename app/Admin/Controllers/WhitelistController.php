<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Grid\BatchCreateWhitelist;
use App\Models\Whitelist;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Http\Request;

class WhitelistController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $model = new Whitelist();
        $model = $model->with(["user", "item"]);
        return Grid::make($model, function (Grid $grid) {
            $grid->column('id')->sortable();
            // $grid->column('user_id');
            $grid->column('user.mobile', "手机号");
            // $grid->column('item_id');
            $grid->column('item.name', "商品名");
            $grid->column('amount');
            // $grid->column('created_at');
            // $grid->column('updated_at')->sortable();

            $grid->tools(new BatchCreateWhitelist());

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
        return Show::make($id, new Whitelist(), function (Show $show) {
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
        return Form::make(new Whitelist(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->text('item_id');
            $form->text('amount');
        
            // $form->display('created_at');
            // $form->display('updated_at');
        });
    }
}
