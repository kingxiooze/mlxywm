<?php

namespace App\Admin\Controllers;

use App\Models\PayChannel;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class PayChannelController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new PayChannel(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('name');
            $grid->column('title');
            $grid->column('pay_type');
            $grid->column('sort');
            // $grid->column('hidden_at');
            $grid->column('is_show', "是否显示")->display(function(){
                return empty($this->hidden_at);
            })->bool();
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
        return Show::make($id, new PayChannel(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('pay_type');
            $show->field('sort');
            $show->field('hidden_at');
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
        return Form::make(new PayChannel(), function (Form $form) {
            $form->display('id');
            $form->text('name');
            $form->text('title');
            $form->text('pay_type');
            $form->text('sort');
            $form->switch('hidden_at', "是否显示")->saving(function ($hidden_at) {
                if ($hidden_at) {
                    return null;
                } else {
                    return now();
                }
            })->customFormat(function($hidden_at){
                if (empty($hidden_at)) {
                    return true;
                } else {
                    return false;
                }
            });
        
            // $form->display('created_at');
            // $form->display('updated_at');
        });
    }
}
