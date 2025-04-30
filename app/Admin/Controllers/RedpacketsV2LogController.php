<?php

namespace App\Admin\Controllers;

use App\Models\RedpacketsV2Log;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class RedpacketsV2LogController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new RedpacketsV2Log(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('redpacket_id');
            $grid->column('amount');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();
        
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
        return Show::make($id, new RedpacketsV2Log(), function (Show $show) {
            $show->field('id');
            $show->field('redpacket_id');
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
        return Form::make(new RedpacketsV2Log(), function (Form $form) {
            $form->display('id');
            $form->text('redpacket_id');
            $form->text('amount');
            $form->text('user_id');
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
