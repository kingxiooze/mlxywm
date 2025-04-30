<?php

namespace App\Http\Controllers;

use App\Models\MoneyLog;
use App\Models\User;
use App\Models\Item;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TeamController extends Controller
{

    protected function getRepositoryClass(){
        return UserRepository::class;
    }

    // 团队统计接口
    public function getTeamState(Request $request) {
        $user = auth()->user();
        $user_id = $user->id;
        // $user_id = 6;

        $lv1_query = User::where("lv1_superior_id", $user_id);
        $lv1_count = $lv1_query->count();
        $lv1_users = $lv1_query->get()->pluck("id");
        $lv1_earning = MoneyLog::where("user_id", $user_id)
            ->where("log_type", 4)
            ->whereHas("userItem", function(Builder $query) use ($lv1_users){
                $query->whereIn("user_id", $lv1_users);
            })
            ->sum("money");
        $lv1_rebate = MoneyLog::where("user_id", $user_id)
            ->where("log_type", 3)
            ->whereIn("source_uid",$lv1_users)
            ->sum("money");
        $lv1_recharge = MoneyLog::whereIn("user_id", $lv1_users)
            ->where("log_type", 1)
            ->sum("money");
        
        $lv2_query = User::where("lv2_superior_id", $user_id);
        $lv2_count = $lv2_query->count();
        $lv2_users = $lv2_query->get()->pluck("id");
        $lv2_earning = MoneyLog::where("user_id", $user_id)
            ->where("log_type", 4)
            ->whereHas("userItem", function(Builder $query) use ($lv2_users){
                $query->whereIn("user_id", $lv2_users);
            })
            ->sum("money");
         $lv2_rebate = 0;
        $lv2_recharge = MoneyLog::whereIn("user_id", $lv2_users)
            ->where("log_type", 1)
            ->sum("money");

        $lv3_query = User::where("lv3_superior_id", $user_id);
        $lv3_count = $lv3_query->count();
        $lv3_users = $lv3_query->get()->pluck("id");
        $lv3_earning = MoneyLog::where("user_id", $user_id)
            ->where("log_type", 4)
            ->whereHas("userItem", function(Builder $query) use ($lv3_users){
                $query->whereIn("user_id", $lv3_users);
            })
            ->sum("money");
        $lv3_rebate =0;
        $lv3_recharge = MoneyLog::whereIn("user_id", $lv3_users)
            ->where("log_type", 1)
            ->sum("money");

        // 日消费任务金统计

        // 计算统计时间范围
        // $time_limit = setting("STAT_TIME_LIMIT", "02:00");
        // $start_time = now()->previous($time_limit);
        // $end_time = now()->next($time_limit);

        // // 等级1下级统计
        // $lv1_mb_cost = MoneyLog::whereIn("user_id", $lv1_users)
        //     ->where("log_type", 6)
        //     ->where("balance_type", 3)
        //     ->where("created_at", ">=", $start_time)
        //     ->where("created_at", "<", $end_time)
        //     ->sum("money");
        // $lv1_mb_cost = abs($lv1_mb_cost);

        // // 等级2下级统计
        // $lv2_mb_cost = MoneyLog::whereIn("user_id", $lv2_users)
        //     ->where("log_type", 6)
        //     ->where("balance_type", 3)
        //     ->where("created_at", ">=", $start_time)
        //     ->where("created_at", "<", $end_time)
        //     ->sum("money");
        // $lv2_mb_cost = abs($lv2_mb_cost);
        

        // // 等级3下级统计
        // $lv3_mb_cost = MoneyLog::whereIn("user_id", $lv3_users)
        //     ->where("log_type", 6)
        //     ->where("balance_type", 3)
        //     ->where("created_at", ">=", $start_time)
        //     ->where("created_at", "<", $end_time)
        //     ->sum("money");
        // $lv3_mb_cost = abs($lv3_mb_cost);
        

        return $this->success([
            "lv1_count" => $lv1_count,
            "lv1_earning" => $lv1_earning,
            "lv1_rebate" => $lv1_rebate,
            "lv2_count" => $lv2_count,
            "lv2_earning" => $lv2_earning,
            "lv2_rebate" => $lv2_rebate,
            "lv3_count" => $lv3_count,
            "lv3_earning" => $lv3_earning,
            "lv3_rebate" => $lv3_rebate,
            // // 日消费任务金统计
            "lv1_recharge" => $lv1_recharge,
            "lv2_recharge" => $lv2_recharge,
            "lv3_recharge" => $lv3_recharge,
        ]);

    }

    // 团队下级用户列表
    public function getTeamInferior(Request $request) {
        $team_lv = $request->input("lv", "1");
        $user = auth()->user();

        $srecharge_sq = DB::table("money_log")
            ->selectRaw("SUM(money)")
            ->where("log_type", 1)
            ->where("user_id", DB::raw("`users`.`id`"));

        $query_key = "lv" . $team_lv ."_superior_id";
        $paginator = User::where($query_key, $user->id)
            // ->orderBy("created_at", "desc")
            // 20230919: 根据充值金额倒序
            ->addSelect([
                "recharge_money" => $srecharge_sq
            ])
            ->orderBy("recharge_money", "desc")
            ->paginate(10);

        $paginator->getCollection()->transform(function ($value) {
            // 20230919: 根据充值金额倒序
            // $recharge_money = MoneyLog::where("user_id", $value->id)
            //     ->where("log_type", 1)
            //     ->sum("money");
            return [
                "name" => $value->name,
                "avatar" => $value->avatar,
                "created_at"=>$value->created_at,
                // "mobile" => Str::mask($value->mobile, "*", 4),
                // 20230919: 里面的手机号隐藏修改一下，改成前4后3显示中间隐藏，他们的号码一定是12位
                "mobile" => Str::mask($value->mobile, "*", 4, 5),
                // "recharge_money" => $recharge_money
                // 20230919: 根据充值金额倒序
                "recharge_money" => $value->recharge_money
            ];
        });

        return $this->success($paginator);
    }

    // 团队收益记录
    public function getTeamCommission(Request $request) {
        $user = auth()->user();
        $user_id = $user->id;
        // $user_id = 6;
        // $inferior = User::where("lv1_superior_id", $user_id)
        //     ->orWhere("lv2_superior_id", $user_id)
        //     ->orWhere("lv3_superior_id", $user_id)
        //     ->get()
        //     ->pluck("id");
        $type=$request->input("lv", "1");
        if($type==1){
             $inferior=User::where("lv1_superior_id", $user_id)
                ->get()
                ->pluck("id");
        }else if($type==2){
             $inferior=User::where("lv2_superior_id", $user_id)
                ->get()
                ->pluck("id");
        }else{
             $inferior=User::where("lv3_superior_id", $user_id)
                ->get()
                ->pluck("id");
        }
       
        $paginator = MoneyLog::where("log_type", 4)
            ->where("user_id", $user_id)
            ->whereIn("source_uid",$inferior)
            ->orderBy("created_at", "desc")
            ->paginate(10);
        $paginator->getCollection()->transform(function ($value) {
            if ($value->userItem && $value->userItem->user) {
                $user = [
                    "name" => $value->userItem->user->name,
                    "avatar" => $value->userItem->user->avatar,
                    "id" => $value->userItem->user->id,
                ];
            } else {
                $user = null;
            }

            if ($value->userItem && $value->userItem->item) {
                $item = [
                    "name" => $value->userItem->item->name,
                    "image" => $value->userItem->item->image,
                    "id" => $value->userItem->item->id,
                ];
            } else {
                $item = null;
            }
            
            
            $value->pay_user = $user;
            $value->pay_item = $item;
            $value->unsetRelations();
            return $value;
        });
        return $this->success($paginator);
        
    }
    
    // 返现记录
    public function getTeamCashback(Request $request) {
        $user = auth()->user();
        $user_id = $user->id;
        // $user_id = 6;
        // $inferior = User::where("lv1_superior_id", $user_id)
        //     ->orWhere("lv2_superior_id", $user_id)
        //     ->orWhere("lv3_superior_id", $user_id)
        //     ->get()
        //     ->pluck("id");
        $type=$request->input("lv", "1");
        if($type==1){
             $inferior=User::where("lv1_superior_id", $user_id)
                ->get()
                ->pluck("id");
        }else if($type==2){
             $inferior=User::where("lv2_superior_id", $user_id)
                ->get()
                ->pluck("id");
        }else{
             $inferior=User::where("lv3_superior_id", $user_id)
                ->get()
                ->pluck("id");
        }
       
        $paginator = MoneyLog::where("log_type", 3)
            ->where("user_id", $user_id)
            ->whereIn("source_uid",$inferior)
            ->orderBy("created_at", "desc")
            ->paginate(10);
        $paginator->getCollection()->transform(function ($value) {
            if ($value->sourceUser) {
                $user = [
                    "name" => $value->sourceUser->name,
                    "avatar" => $value->sourceUser->avatar,
                    "id" => $value->sourceUser->id,
                ];
            } else {
                $user = null;
            }
           
            if ($value->item) {
                $item = [
                    "name" => $value->item->name,
                    "image" => $value->item->image,
                    "id" => $value->item->id,
                ];
            } else {
                $item = null;
            }
            
            
            $value->pay_user = $user;
            $value->pay_item = $item;
            $value->unsetRelations();
            return $value;
        });
        return $this->success($paginator);
        
    }
    // 获取团队流水奖励设置
    public function getTeamStatementBonusSetting() {
        $bonus4_raw = setting("DAILY_TEAM_STATEMENT_BONUS_4");
        $bonus4 = explode(";", $bonus4_raw);

        $bonus3_raw = setting("DAILY_TEAM_STATEMENT_BONUS_3");
        $bonus3 = explode(";", $bonus3_raw);

        $bonus2_raw = setting("DAILY_TEAM_STATEMENT_BONUS_2");
        $bonus2 = explode(";", $bonus2_raw);

        $bonus1_raw = setting("DAILY_TEAM_STATEMENT_BONUS_1");
        $bonus1 = explode(";", $bonus1_raw);

        return $this->success([
            "bonus4" => [
                "limit" => $bonus4[0],
                "reward" => $bonus4[1],
            ],
            "bonus3" => [
                "limit" => $bonus3[0],
                "reward" => $bonus3[1],
            ],
            "bonus2" => [
                "limit" => $bonus2[0],
                "reward" => $bonus2[1],
            ],
            "bonus1" => [
                "limit" => $bonus1[0],
                "reward" => $bonus1[1],
            ],
        ]);
    }
    // 团队下级用户统计
    // 功能其实和getTeamState这个函数是一样的，只是计算比较多，拆分一个接口出来
    public function getTeamInferiorState(Request $request) {
        $user = auth()->user();
        $type = $request->input("type", 1);
        if($type==1){
             // 购买过商品的邀请用户数量
        $ownitem_invite = User::where("lv1_superior_id", $user->id)
            ->whereDate("created_at", ">=", today()->startOfWeek())
            ->whereDate("created_at", "<=", today()->endOfWeek())
            ->has("own_item", ">", "0")
            ->count();
         // 购买过商品的2级用户
        $ownitem_invite_lv2 = User::where("lv2_superior_id", $user->id)
            ->whereDate("created_at", ">=", today()->startOfWeek())
            ->whereDate("created_at", "<=", today()->endOfWeek())
            ->has("own_item", ">", "0")
            ->count();
         // 购买过商品的3级用户
        $ownitem_invite_lv3 = User::where("lv3_superior_id", $user->id)
            ->whereDate("created_at", ">=", today()->startOfWeek())
            ->whereDate("created_at", "<=", today()->endOfWeek())
            ->has("own_item", ">", "0")
            ->count();
        }else{
        $ownitem_invite = User::where("lv1_superior_id", $user->id)
             ->whereYear("created_at", today()->year)
            ->whereMonth("created_at", today()->month)
            ->has("own_item", ">", "0")
            ->count();
         // 购买过商品的2级用户
        $ownitem_invite_lv2 = User::where("lv2_superior_id", $user->id)
             ->whereYear("created_at", today()->year)
            ->whereMonth("created_at", today()->month)
            ->has("own_item", ">", "0")
            ->count();
         // 购买过商品的3级用户
        $ownitem_invite_lv3 = User::where("lv3_superior_id", $user->id)
            ->whereYear("created_at", today()->year)
            ->whereMonth("created_at", today()->month)
            ->has("own_item", ">", "0")
            ->count();  
        }
       
        return $this->success([
            "ownitem_invite" => $ownitem_invite,
            "ownitem_invite_bc" => $ownitem_invite_lv2+$ownitem_invite_lv3,
        ]);
    }
    
    
     public function getTeamforState(Request $request) {
        $user = auth()->user();
        $type = $request->input("type", 0);
        $DataList = array();
        if($type==0){
        $weeklist = explode("|", setting("SALARY_WEEK_STATE", "0"));
       
        foreach ($weeklist as $item) {
            $weekitem = explode(".", $item);
                  // 购买过商品的邀请用户数量
            
            $ownitem_invite = User::where("lv1_superior_id", $user->id)
            ->whereDate("created_at", ">=", $weekitem[0])
            ->whereDate("created_at", "<=", $weekitem[1])
            ->has("own_item", ">", "0")
            ->count();
            $weekstate=["ownitem_invite"=> $ownitem_invite,"startTime"=>$weekitem[0],"endTime"=>$weekitem[1]];
            array_push($DataList,$weekstate);
        }
        }else{
            $monthlist = explode("|", setting("SALARY_MONTH_STATE", "0"));
       
        foreach ($monthlist as $item) {
            $monthitem = explode(".", $item);
                  // 购买过商品的邀请用户数量
            
            $ownitem_invite = User::where("lv1_superior_id", $user->id)
            ->whereDate("created_at", ">=", $monthitem[0])
            ->whereDate("created_at", "<=", $monthitem[1])
            ->has("own_item", ">", "0")
            ->count();
                // 购买过商品的2级用户
        $ownitem_invite_lv2 = User::where("lv2_superior_id", $user->id)
            ->whereDate("created_at", ">=", $monthitem[0])
            ->whereDate("created_at", "<=", $monthitem[1])
            ->has("own_item", ">", "0")
            ->count();
         // 购买过商品的3级用户
        $ownitem_invite_lv3 = User::where("lv3_superior_id", $user->id)
            ->whereDate("created_at", ">=", $monthitem[0])
            ->whereDate("created_at", "<=", $monthitem[1])
            ->has("own_item", ">", "0")
            ->count();
            $monthstate=["ownitem_invite"=> $ownitem_invite,"startTime"=>$monthitem[0],"endTime"=>$monthitem[1],
            "ownitem_invite_lv2"=>$ownitem_invite_lv2,
            "ownitem_invite_lv3"=>$ownitem_invite_lv3,
            ];
            array_push($DataList,$monthstate);
        } 
        }
        //  // 购买过商品的2级用户
        // $ownitem_invite_lv2 = User::where("lv2_superior_id", $user->id)
        //     ->whereDate("created_at", ">=", $reg_reward[0])
        //     ->whereDate("created_at", "<=", $reg_reward[1])
        //     ->has("own_item", ">", "0")
        //     ->count();
        //  // 购买过商品的3级用户
        // $ownitem_invite_lv3 = User::where("lv3_superior_id", $user->id)
        //     ->whereDate("created_at", ">=", $reg_reward[0])
        //     ->whereDate("created_at", "<=", $reg_reward[1])
        //     ->has("own_item", ">", "0")
        //     ->count();
        
       
        return $this->success($DataList);
    }
       //返现邀请统计 
      public function getTodayCashbackState(Request $request) {
        $user = auth()->user();
         $data = [
            "rewards" => []
        ];
        $items = Item::orderBy("price", "asc")
            ->where("is_sell", 1)
            ->get();
        foreach ($items as $item) {
            $r = MoneyLog::where("user_id", $user->id)
                ->where("item_id", $item->id)
                ->where("log_type", 3)
                ->groupBy("item_id")
                ->sum("money");
             $r2 = MoneyLog::where("user_id", $user->id)
                ->where("item_id", $item->id)
                ->where("log_type", 3)
                ->whereDay("created_at",today())
                ->groupBy("item_id")
                ->sum("money");
            $r3 = MoneyLog::where("user_id", $user->id)
                ->where("item_id", $item->id)
                ->where("log_type", 3)
                ->whereDay("created_at",today())
                ->groupBy("item_id")
                ->count();
                // ->pluck("c", "status")
                // ->toArray()
            array_push($data["rewards"], 
                array_merge(
                    $item->toArray(), 
                    [
                        "totalMoney" => $r,
                        "todayMoney" => $r2,
                        "count"=>$r3
                    ]
                )
            );
        }
        
        
        
        $totalMoney = MoneyLog::where("user_id",$user->id)
                    ->where("log_type",3)
                    ->sum("money");
        $todayMoney = MoneyLog::where("user_id",$user->id)
                    ->where("log_type",3)
                    ->whereDay("created_at",today())
                    ->sum("money"); 
       
        return $this->success($data);
    }
}
