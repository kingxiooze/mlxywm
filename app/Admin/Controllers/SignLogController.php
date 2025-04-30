<?php

namespace App\Admin\Controllers;

use App\Models\SignLog;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class SignLogController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new SignLog(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('user_id');
            $grid->column('reward');
            $grid->column('signed_at');
            $grid->column('duration_day');
        
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
        return Show::make($id, new SignLog(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('reward');
            $show->field('signed_at');
            $show->field('duration_day');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new SignLog(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->text('reward');
            $form->text('signed_at');
            $form->text('duration_day');
        });
    }
}
