<?php

namespace App\SalesAdmin\Controllers;

use App\Admin\Actions\Grid\UserBan;
use App\Admin\Actions\Grid\UserResetPwd;
use App\Admin\Actions\Grid\UserResetBankcard;
use App\Models\MoneyLog;
use App\Models\User;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Hash;
use App\Admin\Actions\Form\ChangeUserBalance;

class UserController extends AdminController
{

    protected $user_type = 1;

    public function __construct()
    {
        if (request()->path() == "sales-admin/user_has_recharge") {
            $this->user_type = 2;
        } else if (request()->path() == "sales-admin/user_donthave_recharge") {
            $this->user_type = 3;
        } else if (request()->path() == "sales-admin/user_state") {
            $this->user_type = 4;
        } else {
            $this->user_type = 1;
        }
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $model = new User();
        $model = $model->with("parent");
        return Grid::make($model, function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('name');
            $grid->column('mobile', "mobile");
            $grid->column('email', "email")->limit(10);
            $grid->column('parent.name', "superior username");
            $grid->column('parent.mobile', "superior mobile");
           
                $grid->column("invite_count", "Number of Invites")->display(function(){
                    $c = User::where("parent_code", $this->code)->count();
                    if ($c == 0) {
                        return 0;
                    }
                    $url = request()->fullUrl();
                    $url_parts = parse_url($url);
                    // if (isset($url_parts['query'])) { // Avoid 'Undefined index: query'
                    //     parse_str($url_parts['query'], $params);
                    // } else {
                    //     $params = array();
                    // }
                    $params = array();
                    $params["page"] = 1;
                    $params["parent_code"] = $this->code;
                    $url_parts['query'] = http_build_query($params);
                    $link = $url_parts['scheme'] . '://' . $url_parts['host'] . $url_parts['path'] . '?' . $url_parts['query'];
                    return sprintf("<a href='%s'>%s</a>", $link, $c);
                });
                // $grid->column("total_yj", "总佣金")->display(function(){
                //     return MoneyLog::where("user_id", $this->id)
                //         ->where("log_type", 4)
                //         ->sum("money");
                // });
                // $grid->column("total_sy", "总收益")->display(function(){
                //     return MoneyLog::where("user_id", $this->id)
                //         ->where("log_type", 5)
                //         ->sum("money");
                // });
                $grid->column("total_cz", "Total deposit")->display(function(){
                    return MoneyLog::where("user_id", $this->id)
                        ->where("log_type", 1)
                        ->sum("money");
                });
                $grid->column("total_tx", "Total withdrawal")->display(function(){
                    $r = MoneyLog::where("user_id", $this->id)
                        ->where("log_type", 2)
                        ->sum("money");
                    return abs($r);
                });
         
            $grid->column('balance')->sortable();
            $grid->column('created_at', "create time")
                ->sortable()
                ->display(function($value){
                    return $value->toDateTimeString();
                });
            // $grid->column("baned_at", "封禁")->action(
            //     UserBan::class
            // );
            // $grid->column("reset_pwd", "重置密码")->action(
            //     UserResetPwd::class
            // );
            // $grid->column("reset_bankcard", "重置银行卡")->action(
            //     UserResetBankcard::class
            // );
            // $grid->column("buy_log", "购买记录")->display(function(){
            //     return "购买记录";
            // })
            // ->link(function ($value) {
            //     return admin_url('user_item?user_id='.$this->id);
            // });
            
            $grid->model()->orderBy('id', 'desc');

            $subordinates = Admin::user();
            $grid->model()->where("salesman_code", $subordinates->code);

            if ($this->user_type == 2) {
                // 有效用户(充值用户)
                $grid->model()->where("is_recharged_or_buyed", 1);
            } else if ($this->user_type == 3) {
                // 无效用户(未充值用户)
                $grid->model()->where("is_recharged_or_buyed", 0);
            }
            
            $grid->disableDeleteButton();
            $grid->disableBatchDelete();
            $grid->disableActions();  
            $grid->disableCreateButton();
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->equal('name');
                $filter->equal('mobile');
                $filter->between('created_at', "created time")->datetime();
                $filter->equal("parent_code");
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
    // protected function detail($id)
    // {
    //     return Show::make($id, new User(), function (Show $show) {
    //         $show->field('id');
    //         $show->field('name');
    //         $show->field('avatar');
    //         $show->field('mobile');
    //         // $show->field('password');
    //         // $show->field('trade_password');
    //         $show->field('parent_code');
    //         $show->field('code');
    //         $show->field('balance');
    //         $show->field('lv1_superior_id');
    //         $show->field('lv2_superior_id');
    //         $show->field('lv3_superior_id');
    //         $show->field('created_at');
    //         $show->field('updated_at');
    //     });
    // }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    // protected function form()
    // {
    //     return Form::make(new User(), function (Form $form) {
    //         $form->display('id');
    //         $form->text('name');
    //         $form->image('avatar')->autoUpload()->saveFullUrl();
    //         $form->text('mobile');
    //         $form->text('email', "邮箱");
    //         $form->password('password')->customFormat(function () {
    //             return '';
    //         });

    //         $form->password('trade_password')->customFormat(function(){
    //             return '';
    //         });
    //         $form->text('parent_code');
    //         $form->text('code');

    //         // $form->text('balance');

    //         $balanceTool = new ChangeUserBalance();
    //         $balanceTool->setKey($form->model()->id);
    //         $form->html($balanceTool, "增减余额");

    //         $form->text("balance", "可提现余额")->readOnly();
    //         $form->text("redpacket_balance", "红包金")->readOnly();
    //         $form->text("mission_balance", "任务金")->readOnly();
            
    //         $form->text('lv1_superior_id');
    //         $form->text('lv2_superior_id');
    //         $form->text('lv3_superior_id');
    //         $form->switch("is_simple_redpack");
    //         $form->switch("is_salesman");
    //         $form->text("salesman_code");
            
            

    //         // $form->tools(function (Form\Tools $tools) {
    //         //     $tools->append(new ChangeUserBalance());
    //         // });
        
    //     })->saving(function (Form $form) {
    //         if (! $form->password) {
    //             $form->deleteInput('password');
    //         } else {
    //             $form->password = Hash::make($form->password);
    //         }

    //         if (! $form->trade_password) {
    //             $form->deleteInput('trade_password');
    //         } else {
    //             $form->trade_password = Hash::make($form->trade_password);
    //         }
    //     });
    // }
}
