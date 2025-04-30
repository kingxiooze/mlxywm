<?php

namespace App\Admin\Controllers;

use App\Models\User;
use App\Models\UserRealname;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class UserRealnameController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $model = new UserRealname();
        $model = $model->with(["user"]);
        return Grid::make($model, function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('user.mobile', "用户手机号");
            $grid->column('image1')->image('', 50, 50);
            $grid->column('image2')->image('', 50, 50);
            $grid->column('paper_type')->display(function($v){
                $d = "";
                switch ($v) {
                    case 1:
                        $d = "身份证";
                        break;
                    case 2:
                        $d = "护照";
                        break;
                    case 3:
                        $d = "驾照";
                        break;
                    default:
                        $d = "";
                        break;
                }
                return $d;
            });
            $grid->column('paper_code');
            // $grid->column('status')->display(function($v){
            //     $d = "";
            //     switch ($v) {
            //         case 0:
            //             $d = "待审核";
            //             break;
            //         case 1:
            //             $d = "审核通过";
            //             break;
            //         case 2:
            //             $d = "审核失败";
            //             break;
            //         default:
            //             $d = "";
            //             break;
            //     }
            //     return $d;
            // });
            $grid->column('created_at');
            $grid->model()->orderBy('id', 'desc');
        
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
        return Show::make($id, new UserRealname(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('image1');
            $show->field('image2');
            $show->field('paper_type');
            $show->field('paper_code');
            // $show->field('status');
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
        return Form::make(new UserRealname(), function (Form $form) {
            $form->display('id');
            $form->select("user_id", "用户")->options(
                User::query()->pluck("mobile", "id")
            );
            $form->image('image1')->autoUpload()->saveFullUrl();
            $form->image('image2')->autoUpload()->saveFullUrl();
            $form->radio('paper_type')->options([
                "1" => "身份证",
                "2" => "护照",
                "3" => "驾照",
            ]);
            $form->text('paper_code');
            // $form->radio('status')->options([
            //     "0" => "待审核",
            //     "1" => "审核成功",
            //     "2" => "审核失败",
            // ]);
        });
    }
}
