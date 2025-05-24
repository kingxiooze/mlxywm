<?php

namespace App\SalesAdmin\Controllers;

use App\Models\TextContent;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class TextContentController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new TextContent(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('types');
            $grid->column('filename');
            $grid->column('content')->limit(20);
            $grid->column('sort');
            $grid->model()->orderBy('sort', 'desc');
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
        return Show::make($id, new TextContent(), function (Show $show) {
            $show->field('id');
            $show->field('types');
            $show->richHtml('content');
            $show->field('sort');
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
        return Form::make(new TextContent(), function (Form $form) {
            $form->display('id');
            $form->text('types');
            $form->file('filename')->autoUpload()->saveFullUrl();
            $form->editor('content');
            $form->text('sort');
        
            // $form->display('created_at');
            // $form->display('updated_at');
        });
    }
}
