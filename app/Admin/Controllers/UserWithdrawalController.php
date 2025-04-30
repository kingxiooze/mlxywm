<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Grid\WithdrawalBatchPass;
use App\Admin\Actions\Grid\WithdrawalBatchReject;
use App\Admin\Actions\Grid\WithdrawalTransfer;
use App\Admin\Actions\Grid\WithdrawalBatchTransfer;
use App\Models\UserWithdrawal;
use App\Models\MoneyLog;
use App\Repositories\WithdrawalRepository;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class UserWithdrawalController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $model = new UserWithdrawal();
        $model = $model->with(["user"]);
        return Grid::make($model, function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('user.mobile', "手机号");
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
            $grid->column("total_cz", "总充值")->display(function(){
                    return MoneyLog::where("user_id", $this->user_id)
                        ->where("log_type", 1)
                        ->sum("money");
                });
            $grid->column("bankcard_info", "银行卡信息")->display(function(){
                return "银行卡信息";
            })
            ->link(function ($value) {
                return admin_url('user_bankcard/'.$this->bankcard_id);
            });
            $grid->column('amount');
             $grid->column('user.salesman_code',"业务员码");
            $grid->column('amount');
            $grid->column("withdrawal_no", "提现单号");
            $grid->column("order_status", "订单状态")->display(function($v){
                $d = "";
                switch ($v) {
                    case 1:
                        $d = "已提现";
                        break;
                    case 2:
                        $d = "未提现";
                        break;
                    case 3:
                        $d = "提现取消";
                        break;
                    default:
                        $d = "";
                        break;
                }
                return $d;
            });
            $grid->column("pay_status", "支付状态")->display(function($v){
                $d = "";
                switch ($v) {
                    case 1:
                        $d = "已提现";
                        break;
                    case 2:
                        $d = "未提现";
                        break;
                    default:
                        $d = "";
                        break;
                }
                return $d;
            });
             $grid->column("created_at", "创建时间");
            $grid->column("pay_time", "提现时间");
            $grid->column("usdt_receipt.image", "提现截图")->image('', 50, 50);
            $grid->column("pay_type", "提现渠道")->display(function($v){
                $d = "";
                switch ($v) {
                    case 1:
                        $d = "CSPAY";
                        break;
                    case 2:
                        $d = "USDT";
                        break;
                    case 3:
                        $d = "YTPAY";
                        break;
                    case 4:
                        $d = "GSPAY";
                        break;
                    case 5:
                        $d = "WEPAY";
                        break;
                    case 6:
                        $d = "DFPAY";
                        break;
                    case 7:
                        $d = "SharkPAY";
                        break;
                    case 8:
                        $d = "GTPAY";
                        break;
                    case 9:
                        $d = "PPAY";
                        break;
                    case 10:
                        $d = "MPay";
                        break;
                    case 11:
                        $d = "FFPay";
                        break;
                    case 12:
                        $d = "XDPAY-X1";
                        break;
                    case 13:
                        $d = "XDPAY-DGM";
                        break;
                    case 14:
                        $d = "XDPAY-X2";
                        break;
                    case 15:
                        $d = "WOWPay";
                        break;
                    default:
                        $d = "UNSUPPORT";
                        break;
                }
                return $d;
            });

            $grid->model()->orderBy('id', 'desc');

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                // $actions->append(new WithdrawalTransfer("线上打款-MPAY"));
                $actions->append(new WithdrawalTransfer("线上打款-FFPAY"));
                // $actions->append(new WithdrawalTransfer("线下打款-USDT"));
                // $actions->append(new WithdrawalTransfer("线上打款-XDPAY-X1"));
                // $actions->append(new WithdrawalTransfer("线上打款-XDPAY-DGM"));
                // $actions->append(new WithdrawalTransfer("线上打款-XDPAY-X2"));
                $actions->append(new WithdrawalTransfer("线上打款-SHARKPAY"));
                $actions->append(new WithdrawalTransfer("线上打款-WOWPAY"));
                // $actions->append(new WithdrawalTransfer("线上打款-PPAY"));
                // $actions->append(new WithdrawalTransfer("线上打款-PTMPAY"));
            });

            $grid->batchActions(function(Grid\Tools\BatchActions $actions){
                $actions->add(new WithdrawalBatchPass());
                $actions->add(new WithdrawalBatchReject());
                $actions->add(new WithdrawalBatchTransfer());
            });

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->equal('user.mobile', "用户手机号");
                $filter->equal('withdrawal_no', "订单号");
                $filter->equal("order_status", "订单状态")->select([
                    "2" => "未提现",
                    "1" => "已提现"
                ]);
                $filter->equal("status", "审核状态")->select([
                    "0" => "未审核",
                    "1" => "审核通过",
                    "2" => "审核拒绝",
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
        return Show::make($id, new UserWithdrawal(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('status');
            $show->field('bankcard_id');
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
        $model = new UserWithdrawal();
        $model = $model->with(["user"]);
        return Form::make($model, function (Form $form) {
            $form->text('user.mobile', "手机号")->readonly();
            $form->text('user.balance', "当前余额")->readonly();
            $form->text('amount')->readOnly();
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
            $form->disableDeleteButton();
            $form->disableListButton();
            $form->disableViewButton();
            
            // $form->saving(function (Form $form) {
            //     // 判断是否是新增操作
            //     if (!$form->isCreating()) {
            //         $drawal = $form->model();
            //         if ($drawal->user->balance < $drawal->amount) {
            //             return $form->response()->error('余额不足');
            //         }
            //     }
                
            // });

            $form->saved(function (Form $form) {
                // 判断是否是新增操作
                if (!$form->isCreating()) {
                    
                    $drawal = $form->model();
                    if ($drawal->status == 2) {
                        $drawal->afterRejected();
                    }
                    
                }
                
            });
        });
    }
}
