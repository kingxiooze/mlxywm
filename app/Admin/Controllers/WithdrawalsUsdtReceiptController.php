<?php

namespace App\Admin\Controllers;

use App\Models\UserWithdrawal;
use App\Models\WithdrawalsUsdtReceipt;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class WithdrawalsUsdtReceiptController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new WithdrawalsUsdtReceipt(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('withdrawal_no');
            $grid->column('chain');
            $grid->column('wallet_address');
            $grid->column('image');
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
        return Show::make($id, new WithdrawalsUsdtReceipt(), function (Show $show) {
            $show->field('id');
            $show->field('withdrawal_no');
            $show->field('chain');
            $show->field('wallet_address');
            $show->field('image');
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
        return Form::make(new WithdrawalsUsdtReceipt(), function (Form $form) {
    
            $withdrawal_no = null;
            if ($form->isCreating()) {
                $withdrawal_no = request("withdrawal_no");
                $form->text('withdrawal_no')
                    ->readOnly()
                    ->value($withdrawal_no);
                
            } else {
                $withdrawal_no = $form->model()->withdrawal_no;
                $form->text('withdrawal_no')->readOnly();
            }
            $drawal = UserWithdrawal::where("withdrawal_no", $withdrawal_no)->first();
            
            if ($drawal && $drawal->bankcard) {
                $form->text("bankcard_wallet_chain", "收款链")
                    ->value($drawal->bankcard->wallet_chain)
                    ->readOnly();
                $form->text("bankcard_wallet_address", "收款钱包地址")
                    ->value($drawal->bankcard->wallet_address)
                    ->readOnly();
            }

            $form->divider("打款信息");
            
            $form->select('chain', "打款链")->options([
                "tron" => "TRON",
                "bsc" => "BSC",
            ]);
            $form->text('wallet_address', "打款钱包地址");
            $form->image('image');

            $form->ignore(["bankcard_wallet_chain", "bankcard_wallet_address"]);

            $form->footer(function ($footer) {
                // 去掉`查看`checkbox
                $footer->disableViewCheck();

                // 去掉`继续编辑`checkbox
                $footer->disableEditingCheck();

                // 去掉`继续创建`checkbox
                $footer->disableCreatingCheck();
            });

            $form->saved(function (Form $form, $result) {
                if ($form->isCreating()) {
                    // 自增ID
                    $newId = $result;
            
                    if (! $newId) {
                        return $form->error('数据保存失败');
                    }

                    $receipt = WithdrawalsUsdtReceipt::find($newId);
                    $receipt->withdrawal->pay_type = 2;
                    $receipt->withdrawal->status = 1;
                    $receipt->withdrawal->online_transfer();
            
                    return $form->response()
                        ->success('操作成功')
                        ->redirect('/user_withdrawal');;
                }
            });
        });
    }
}
