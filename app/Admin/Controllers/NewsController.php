<?php

namespace App\Admin\Controllers;

use App\Models\News;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use App\Models\ItemCategory;

class NewsController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $model = new News();
        $model = $model->with(["item_category"]);
        return Grid::make($model, function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('title')->limit(20);
            $grid->column('image')->image('', 50, 50);
            $grid->column('description')->limit(20);
            // $grid->column('content');
            $grid->column('item_category.name', "分类名称");

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
        return Show::make($id, new News(), function (Show $show) {
            $show->field('id');
            $show->field('title');
            $show->field('image');
            $show->field('description');
            $show->field('content');
            $show->field('item_category_id');
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
        return Form::make(new News(), function (Form $form) {
            $form->text('title');
            $form->image('image')->autoUpload()->saveFullUrl();
            $form->textarea('description');
            $form->editor('content');

            $form->select("item_category_id", "商品分类")->options(
                ItemCategory::query()->pluck("name", "id")
            );
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
