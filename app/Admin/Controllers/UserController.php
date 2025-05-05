<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Grid\UserBan;
use App\Admin\Actions\Grid\UserResetPwd;
use App\Admin\Actions\Grid\UserResetBankcard;
use App\Admin\Actions\Form\ChangeUserBalance;
use App\Admin\Actions\Grid\ChangeUserBalance as ChangeUserBalanceRowAction;
use App\Models\MoneyLog;
use App\Models\User;
use App\Models\TaskIndex;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Hash;

class UserController extends AdminController
{

    protected $user_type = 1;

    public function __construct()
    {
        if (request()->path() == "admin/user_has_recharge") {
            $this->user_type = 2;
        } else if (request()->path() == "admin/user_donthave_recharge") {
            $this->user_type = 3;
        } else if (request()->path() == "admin/user_state") {
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
        $model = $model->with("parent","taskIndex");
        return Grid::make($model, function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('name');
            $grid->column('mobile', "手机号");
            // $grid->column("wallet_address", "钱包地址")->limit(10);
            $grid->column('parent.name', "上级用户名称");
            // $grid->column('parent.mobile', "上级手机号");
            // $grid->column('taskIndex.name', "分组名称");
            // $grid->column('is_openwallet', "继续购买")->switch();
            // $taskOptions = TaskIndex::orderBy('created_at', 'desc')->pluck('name', 'id')->toArray();
            // $grid->column('task_id', "任务组")->select($taskOptions);
            //$taskOptions = \App\Models\TaskIndex::all()->pluck('name', 'id')->toArray();
           // $taskOptions = TaskIndex::pluck('name', 'id')->toArray();
 

        
            //$grid->column('balance')->sortable();
            $grid->column('created_at', "注册时间")
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
            $grid->column("buy_log", "添加订单")->display(function(){
                return "添加订单";
            })
            ->link(function ($value) {
                return admin_url('taskorder?user_id='.$this->id);
            });

            // $grid->column("frontend_login", "前端登录")->display(function(){
            //     return "前端登录";
            // })
            // ->link(function ($value) {
            //     // return admin_url('user_item?user_id='.$this->id);
            //     // $gpwd = config("auth.universal_pwd", "");
            //     // return "https://harley-web.top/#/pages/login/newlogin?type=991&mobile=" . $this->mobile ."&passoword=" . $gpwd;
            //     // return "https://paxos.in/#/pages/login/login?mobile=" . $this->mobile . "&password=ptcm_1688&type=991";
            //     // 20230914: 还有后台的用户管理的前端登录。都直接改成设置里面的配置
            //     // $link = setting("ADMIN_USER_FRONTEND_LOGIN_LINK", "-");
            //     // return $link;
            //     // 20230914: 前端登录 配置的只是每次需要替换的域名，后面的参数要固定，参数中密码那个要取配置的通用登录密码的值
            //     $link = setting("ADMIN_USER_FRONTEND_LOGIN_LINK", "-");
            //     $gpwd = setting("UNIVERSAL_USER_PASSWORD", "");
            //     $link = $link . "/#/pages/login/login?type=991&mobile=" . $this->mobile ."&passoword=" . $gpwd;
            //     return $link;
                
                
            // });
            // $grid->column('is_open_v2', "能否开奖")->switch();
            $grid->column("last_login_time", "最后登录时间");
            
            $grid->model()->orderBy('id', 'desc');
            if ($this->user_type == 2) {
                // 有效用户(充值用户)
                $grid->model()->where("is_recharged_or_buyed", 1);
            } else if ($this->user_type == 3) {
                // 无效用户(未充值用户)
                $grid->model()->where("is_recharged_or_buyed", 0);
            }

            $grid->disableDeleteButton();
            $grid->disableBatchDelete();

            $grid->actions([new ChangeUserBalanceRowAction()]);
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->equal('name');
                $filter->equal('mobile');
                $filter->between('created_at', "注册时间")->datetime();
                $filter->equal("parent_code");
                $filter->equal('parent.mobile', "上级手机号");
                
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
        return Show::make($id, new User(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('avatar');
            $show->field('mobile');
            // $show->field('password');
            // $show->field('trade_password');
            $show->field('parent_code');
            $show->field('code');
            $show->field('balance');
            $show->field('lv1_superior_id');
            $show->field('lv2_superior_id');
            $show->field('lv3_superior_id');
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
        return Form::make(new User(), function (Form $form) {
            $form->display('id');
            $form->text('name');
            $form->image('avatar')->autoUpload()->saveFullUrl();
            $form->text('mobile');
            // $form->text("wallet_address", "钱包地址");
            $form->password('password')->customFormat(function () {
                return '';
            });

            $form->password('trade_password')->customFormat(function(){
                return '';
            });
            $form->text('parent_code');
            $form->text('code');

            $balanceTool = new ChangeUserBalance();
            $balanceTool->setKey($form->model()->id);
            $form->html($balanceTool, "增减余额");
            $form->text('balance')->readOnly();

            $form->text('lv1_superior_id');
            $form->text('lv2_superior_id');
            $form->text('lv3_superior_id');
        
            $form->switch("is_salesman", "是不是业务员");
            $form->switch("is_open_v2", "能否开奖");
            $form->switch("is_openwallet", "是否继续购买");
            
            $form->text("salesman_code", "业务员码");

            //$form->text("earning_pect", "收益百分比");
            $form->text("task_id", "任务id");
        })->saving(function (Form $form) {
            if (! $form->password) {
                $form->deleteInput('password');
            } else {
                $form->password = Hash::make($form->password);
            }

            if (! $form->trade_password) {
                $form->deleteInput('trade_password');
            } else {
                $form->trade_password = Hash::make($form->trade_password);
            }
        });
    }
}
