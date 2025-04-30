<?php

namespace App\Admin\Controllers;

use App\Models\UserActiviteCode;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use App\Admin\Actions\Grid\GenerateActiviteCode;

class UserActiviteCodeController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new UserActiviteCode(), function (Grid $grid) {
            $grid->column('id')->sortable();
            // $grid->column('user_id');
            $grid->column('code');
            $grid->column('activite_user');
            $grid->column('activited_at');
            // $grid->column('created_at');
            // $grid->column('updated_at')->sortable();
            $grid->model()->orderBy('id', 'desc');
            $grid->tools(new GenerateActiviteCode());
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
        return Show::make($id, new UserActiviteCode(), function (Show $show) {
            $show->field('id');
            // $show->field('user_id');
            $show->field('code');
            $show->field('activite_user');
            $show->field('activited_at');
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
        return Form::make(new UserActiviteCode(), function (Form $form) {
            $form->display('id');
            // $form->text('user_id');
            $form->text('code');
            $form->text('activite_user');
            $form->text('activited_at');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
