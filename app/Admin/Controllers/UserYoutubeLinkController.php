<?php

namespace App\Admin\Controllers;

use App\Models\UserYoutubeLink;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class UserYoutubeLinkController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new UserYoutubeLink(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('user_id');
            $grid->column('name');
             $grid->column('look_num');
            $grid->column('link')->link();
            $grid->column('image')->image("", 50, 50);
            $grid->column('status')->display(function($v){
                $d = "";
                switch ($v) {
                    case 0:
                        $d = "待审核";
                        break;
                    case 1:
                        $d = "审核通过";
                        break;
                    case 2:
                        $d = "审核拒绝";
                        break;
                    default:
                        $d = "";
                        break;
                }
                return $d;
            });
            $grid->column('sort');
            // $grid->column('created_at');
            // $grid->column('updated_at')->sortable();

            $grid->model()->orderBy("look_num", "asc");
        
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
        return Show::make($id, new UserYoutubeLink(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('name');
            $show->field('link');
            $show->field('image');
            $show->field('status');
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
        return Form::make(new UserYoutubeLink(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->text('name');
            $form->text('link');
            $form->number('look_num',"阅读量");
            $form->image('image')->autoUpload()->saveFullUrl();
            if ($form->model()->status == 0) {
                $form->radio('status')->options([
                    "0" => "待审核",
                    "1" => "审核成功",
                    "2" => "审核拒绝",
                ]);
            } 
            // else {
            //     $form->radio('status')->options([
            //         "0" => "待审核",
            //         "1" => "审核成功",
            //         "2" => "审核失败",
            //     ])->disable();
            //     $form->disableSubmitButton();
            //     $form->disableResetButton();
                
            // }
            $form->text('sort');
        
            // $form->display('created_at');
            // $form->display('updated_at');
        });
    }
}
