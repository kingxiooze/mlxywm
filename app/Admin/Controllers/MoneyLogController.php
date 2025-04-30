<?php

namespace App\Admin\Controllers;

use App\Models\MoneyLog;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class MoneyLogController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $model = new MoneyLog();
        $model = $model->with([
            "user",
            "sourceUser",
            "item",
        ]);
        return Grid::make($model, function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('user.mobile', "用户");
            $grid->column('balance_type', "余额类型")->display(function($v){
                $d = "";
                switch ($v) {
                    case 1:
                        $d = "可提现余额";
                        break;
                    case 2:
                        $d = "红包金";
                        break;
                    case 3:
                        $d = "任务金";
                        break;
                    default:
                        $d = "";
                        break;
                }
                return $d;
            });
            $grid->column('money');
            $grid->column('log_type')->display(function($v){
                $d = "";
                switch ($v) {
                    case 1:
                        $d = "充值";
                        break;
                    case 2:
                        $d = "提现";
                        break;
                    case 3:
                        $d = "返现";
                        break;
                    case 4:
                        $d = "分佣收益";
                        break;
                    case 5:
                        $d = "商品收益";
                        break;
                    case 6:
                        $d = "商品购买";
                        break;
                    case 7:
                        $d = "邀请奖励";
                        break;
                    case 8:
                        $d = "签到奖励";
                        break;
                    case 9:
                        $d = "注册奖励";
                        break;
                    case 10:
                        $d = "退还本金";
                        break;
                    case 11:
                        $d = "邀请红包";
                        break;
                    case 12:
                        $d = "消费金红包";
                        break;
                    case 13:
                        $d = "每日分红";
                        break;
                    case 14:
                        $d = "管理充值";
                        break;
                    case 15:
                        $d = "聊天红包";
                        break;
                     case 19:
                        $d = "充值返现";
                        break;
                         case 20:
                        $d = "提现拒绝返回";
                        break;
                    default:
                        $d = "";
                        break;
                }
                return $d;
            });
            $grid->column('before_change');
            $grid->column('item.name', "商品名称");
            $grid->column('sourceUser.mobile', "来源用户");
            // $grid->column('created_at');
            // $grid->column('updated_at')->sortable();
            $grid->model()->orderBy('id', 'desc');

            $grid->disableActions();
            $grid->disableCreateButton();
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->equal('user.mobile', "用户手机号");
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
        return Show::make($id, new MoneyLog(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('money');
            $show->field('log_type');
            $show->field('before_change');
            $show->field('item_id');
            $show->field('source_uid');
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
        return Form::make(new MoneyLog(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->text('money');
            $form->text('log_type');
            $form->text('before_change');
            $form->text('item_id');
            $form->text('source_uid');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
