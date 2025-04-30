<?php

namespace App\Admin\Controllers;

use App\Models\ReviewTmpl;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class ReviewTmplController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new ReviewTmpl(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('item_id');
            $grid->column('content')->limit(20);
            $grid->column('image')->image('', 50, 50);
            $grid->model()->orderBy('id', 'desc');
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
        return Show::make($id, new ReviewTmpl(), function (Show $show) {
            $show->field('id');
            $show->field('item_id');
            $show->field('content');
            $show->field('image');
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
        return Form::make(new ReviewTmpl(), function (Form $form) {
            $form->display('id');
            $form->text('item_id');
            $form->textarea('content');
            $form->image('image')->autoUpload()->saveFullUrl()->required();
        
            // $form->display('created_at');
            // $form->display('updated_at');
        });
    }
}
