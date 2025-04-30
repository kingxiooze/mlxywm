<?php

namespace App\Admin\Controllers;

use App\Models\UserReadpack;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class UserReadpackController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new UserReadpack(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('user_id');
            $grid->column('open_user');
            $grid->column('opened_at');
            $grid->column('freeze_amount');
            $grid->column('amount');
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
        return Show::make($id, new UserReadpack(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('open_user');
            $show->field('opened_at');
            $show->field('freeze_amount');
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
        return Form::make(new UserReadpack(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->text('open_user');
            $form->text('opened_at');
            $form->text('freeze_amount');
            $form->text('amount');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
