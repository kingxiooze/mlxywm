<?php

namespace App\Admin\Controllers;

use App\Jobs\ItemEarningJob;
use App\Models\ReviewRecord;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class ReviewRecordController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $model = new ReviewRecord();
        $model = $model->with(["user_item.user","user_item.item"]);
        return Grid::make($model, function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('user_item.item.name', "商品名称")->limit(50);
            $grid->column('user_item.item.gain_per_day', "佣金");
            $grid->column('user_item.item.price', "需要消耗任务金");
            // $grid->column('user_item.user.name', "用户名称");
            $grid->column('image')->display(function($image){
                return explode(",", $image);
            })->image('', 50, 50);
            $grid->column('content')->limit(20);
            $grid->column('status')->if(function($column){
                return $column->getValue() == 0;
            })->select([
                "0" => "未审核",
                "1" => "审核通过",
                "2" => "审核失败",
            ])
            ->else()
            ->display(function($v){
                $display_v = null;
                switch ($v) {
                    case 0:
                        $display_v = "未审核";
                        break;
                    case 1:
                        $display_v = "审核通过";
                        break;
                    case 2:
                        $display_v = "审核失败";
                        break;
                    default:
                        $display_v = $v;
                        break;
                }
                return $display_v;
            });
            $grid->model()->orderBy('id', 'desc');
            $grid->disableEditButton();
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
        return Show::make($id, new ReviewRecord(), function (Show $show) {
            $show->field('id');
            $show->field('user_item_id');
            $show->field('tmpl_id');
            $show->field('user_id');
            $show->field('image');
            $show->field('content');
            $show->field('status');
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
        return Form::make(new ReviewRecord(), function (Form $form) {
            $form->display('id');
            $form->text('user_item_id');
            $form->text('tmpl_id');
            $form->text('user_id');
            $form->text('image');
            $form->text('content');
            $form->text('status');
        
            // $form->display('created_at');
            // $form->display('updated_at');

            $form->saved(function(Form $form, $result){
                if (!$form->isCreating()) {
                    $status = $form->model()->status;
                    if ($status == 1) {
                        // ItemEarningJob::dispatch(
                        //     $form->model()->user_item_id
                        // );
                    } else if ($status == 2) {
                        // UserItem.status = 3 审核拒绝
                        $form->model()->user_item->status = 3;
                        $form->model()->user_item->save();
                    }
                }
            });
        });
    }
}
