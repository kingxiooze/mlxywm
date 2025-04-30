<?php

namespace App\Admin\Controllers;

use App\Models\InviteReward;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class InviteRewardController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new InviteReward(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('limit',"邀请人数B");
            $grid->column('invite_numcd',"邀请人数C+D");
            $grid->column('rewards');
            $grid->column('type')->display(function($v){
                $d = "";
                switch ($v) {
                    case 1:
                        $d = "周";
                        break;
                    case 2:
                        $d = "月";
                        break;
                    default:
                        $d = "";
                        break;
                }
                return $d;
            });
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
        return Show::make($id, new InviteReward(), function (Show $show) {
            $show->field('id');
            $show->field('limit');
            $show->field('rewards');
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
        return Form::make(new InviteReward(), function (Form $form) {
            $form->display('id');
            $form->text('limit');
            $form->text('rewards');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
