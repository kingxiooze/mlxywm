<?php

namespace App\Admin\Controllers;

use App\Models\TextContents;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class TextContentsController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new TextContents(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('types');
            $grid->column('cncontent')->limit(20);
            $grid->column('content')->limit(20);
            $grid->column('sort');
            $grid->model()->orderBy('sort', 'asc');
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
        return Show::make($id, new TextContents(), function (Show $show) {
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
        return Form::make(new TextContents(), function (Form $form) {
            $form->display('id');
            $form->text('types');
            $form->editor('cncontent',"中文");
            $form->editor('content');
            $form->text('sort');
        
            // $form->display('created_at');
            // $form->display('updated_at');
        });
    }
}
