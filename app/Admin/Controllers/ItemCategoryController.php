<?php

namespace App\Admin\Controllers;

use App\Models\ItemCategory;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class ItemCategoryController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new ItemCategory(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('name',"店铺名称(英文)");
            $grid->column('cnname',"备注名称(中文)");
            $grid->column('image')->image('', 50, 50);
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
        return Show::make($id, new ItemCategory(), function (Show $show) {
            $show->field('id');
            $show->field('name');
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
        return Form::make(new ItemCategory(), function (Form $form) {
            $form->display('id');
            $form->text('name',"店铺名称(英文)");
            $form->text('cnname',"备注名称(中文)");
            $form->image('image')->autoUpload()->saveFullUrl();
            // $form->display('created_at');
            // $form->display('updated_at');
        });
    }
}
