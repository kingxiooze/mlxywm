<?php

namespace App\SalesAdmin\Controllers;

use App\Models\MoneyLog;
use Dcat\Admin\Admin;
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
            $grid->column('user.mobile', "user mobile");
            // $grid->column('balance_type', "余额类型")->display(function($v){
            //     $d = "";
            //     switch ($v) {
            //         case 1:
            //             $d = "可提现余额";
            //             break;
            //         case 2:
            //             $d = "红包金";
            //             break;
            //         case 3:
            //             $d = "任务金";
            //             break;
            //         default:
            //             $d = "";
            //             break;
            //     }
            //     return $d;
            // });
            $grid->column('money',"variation money");
            $grid->column('log_type',"type")->display(function($v){
                $d = "";
                switch ($v) {
                    case 1:
                        $d = "Deposit";
                        break;
                    case 2:
                        $d = "Withdraw";
                        break;
                    case 3:
                        $d = "Cash back on purchase";
                        break;
                    case 4:
                        $d = "commission";
                        break;
                    case 5:
                        $d = "Lease income";
                        break;
                    case 6:
                        $d = "Lease";
                        break;
                    case 7:
                        $d = "invite award";
                        break;
                    case 8:
                        $d = "sign";
                        break;
                    // case 9:
                    //     $d = "注册奖励";
                    //     break;
                    // case 10:
                    //     $d = "退还本金";
                    //     break;
                    // case 11:
                    //     $d = "邀请红包";
                    //     break;
                    // case 12:
                    //     $d = "消费金红包";
                    //     break;
                    // case 13:
                    //     $d = "每日分红";
                    //     break;
                    case 14:
                        $d = "manager to-up";
                        break;
                    // case 15:
                    //     $d = "聊天红包";
                    //     break;
                    case 16:
                    //     $d = "余额转换为任务金";
                    //     break;
                    // case 17:
                    //     $d = "红包转换为任务金";
                    //     break;
                    default:
                        $d = "";
                        break;
                }
                return $d;
            });
            $grid->column('before_change','before balance');
            $grid->column('item.name', "product");
            $grid->column('sourceUser.mobile', "Source User");
            // $grid->column('created_at');
            // $grid->column('updated_at')->sortable();
            $grid->model()->orderBy('id', 'desc');

            $subordinates = Admin::user()->subordinates();
            $grid->model()->whereIn("user_id", $subordinates);

            $grid->disableActions();
            $grid->disableCreateButton();
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->equal('user.mobile', "user mobile");
                $filter->in('log_type', "type")->multipleSelect([
                    '1' => 'Deposit',
                    '2' => 'Withdraw',
                    // '3' => '返现',
                    // '4' => '分佣收益',
                    '5' => 'Lease income',
                    // '6' => '商品购买',
                    // '7' => '邀请奖励',
                    // '8' => '签到奖励',
                    // '9' => '注册奖励',
                    // '10' => '退还本金',
                    // '11' => '邀请红包',
                    // '12' => '消费金红包',
                    // '13' => '每日分红',
                    // '14' => '管理充值',
                    // '15' => '聊天红包',
                    // '16' => '余额转换为任务金',
                    // '17' => '红包转换为任务金',
                ]);
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
