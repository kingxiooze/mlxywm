<?php

namespace App\Admin\Controllers;

use App\Models\Order;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use App\Admin\Actions\Grid\OrderBatchPass;
use App\Admin\Actions\Grid\OrderBatchReject;

class OrderController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $model = new Order();
        $model = $model->with(["usdt", "user"]);
        return Grid::make($model, function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('order_no');
            // $grid->column('trade_no');
            $grid->column('user.mobile', "用户手机号");
            $grid->column('pay_type')->display(function($v){
                $display_v = null;
                switch ($v) {
                    case 1:
                        $display_v = "CSPAY";
                        break;
                    case 2:
                        $display_v = "USDT";
                        break;
                    case 3:
                        $display_v = "YTPAY";
                        break;
                    case 4:
                        $display_v = "GSPAY";
                        break;
                    case 5:
                        $display_v = "WEPAY";
                        break;
                    case 6:
                        $display_v = "DFPAY";
                        break;
                    case 7:
                        $display_v = "SharkPAY";
                        break;
                    case 8:
                        $display_v = "GTPAY";
                        break;
                    case 9:
                        $display_v = "PPAY";
                        break;
                    case 10:
                        $display_v = "MPay";
                        break;
                    case 11:
                        $display_v = "FFPay";
                        break;
                    case 12:
                        $display_v = "XDPAY-X1";
                        break;
                    case 13:
                        $display_v = "XDPAY-DGM";
                        break;
                    case 14:
                        $display_v = "XDPAY-X2";
                        break;
                    case 15:
                        $display_v = "WOWPay";
                        break;
                    default:
                        $display_v = $v;
                        break;
                }
                return $display_v;
            });
            $grid->column('goods_type')->display(function($v){
                $display_v = null;
                switch ($v) {
                    case 1:
                        $display_v = "充值";
                        break;
                    default:
                        $display_v = $v;
                        break;
                }
                return $display_v;
            });
            $grid->column('money');
            $grid->column('price');
            $grid->column('pay_status')->display(function($v){
                $display_v = null;
                switch ($v) {
                    case 1:
                        $display_v = "已支付";
                        break;
                    case 2:
                        $display_v = "未支付";
                        break;
                    default:
                        $display_v = $v;
                        break;
                }
                return $display_v;
            });
            $grid->column('order_status')->display(function($v){
                $display_v = null;
                switch ($v) {
                    case 1:
                        $display_v = "待付款";
                        break;
                    case 2:
                        $display_v = "已付款";
                        break;
                    case 3:
                        $display_v = "手动到账拒绝";
                        break;
                    case 4:
                        $display_v = "手动到账通过";
                        break;
                    default:
                        $display_v = $v;
                        break;
                }
                return $display_v;
            });
            $grid->column('pay_time');
            $grid->column('created_at', "订单时间");
            $grid->column("usdt_link", "USDT")->display(function(){
                if ($this->usdt) {
                    $link = admin_route(
                        "order_usdt.edit", ["order_usdt" => $this->usdt->id]
                    );
                    $status_display = "USDT";
                    switch ($this->usdt->status) {
                        case 0:
                            $status_display = "待审核";
                            break;
                        case 1:
                            $status_display = "审核通过";
                            break;
                        case 2:
                            $status_display = "审核失败";
                            break;
                        default:
                            $status_display = (string)$this->usdt->status;
                            break;
                    }
                    return "<a href='$link'>$status_display</a>";
                } else {
                    return "";
                }
            });

            $grid->model()->orderBy('id', 'desc');

            $grid->disableCreateButton();

            $grid->batchActions(function(Grid\Tools\BatchActions $actions){
                $actions->add(new OrderBatchPass());
                $actions->add(new OrderBatchReject());
            });
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->equal("user.mobile", "用户手机号");
                $filter->equal("order_no");
                $filter->equal("pay_status", "支付状态(是否收到回调)")->select([
                    "2" => "未支付",
                    "1" => "已支付"
                ]);
                // $filter->where("order_pass_status", function($query){
                //     $query->where("order_status", $this->input);
                // }, "审核状态")->select([
                //     "3" => "未通过",
                //     "4" => "已通过"
                // ]);
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
        return Show::make($id, new Order(), function (Show $show) {
            $show->field('id');
            $show->field('order_no');
            $show->field('trade_no');
            $show->field('user_id');
            $show->field('pay_type');
            $show->field('goods_type');
            $show->field('money');
            $show->field('price');
            $show->field('pay_status');
            $show->field('order_status');
            $show->field('pay_time');
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
        return Form::make(new Order(), function (Form $form) {
            $form->display('id');
            $form->text('order_no');
            $form->text('trade_no');
            $form->text('user_id');
            $form->text('pay_type');
            $form->text('goods_type');
            $form->text('money');
            $form->text('price');
            $form->text('pay_status');
            $form->text('order_status');
            $form->text('pay_time');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
