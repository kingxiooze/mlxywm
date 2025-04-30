<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Models\UserReadpack;
use App\Models\User;
use App\Models\UserActiviteCode;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Class RedPackRepository.
 *
 * @package namespace App\Repositories;
 */
class RedPackRepository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserReadpack::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    // 打开红包
    public function openPack() {
        $user = auth()->user();

        // 用户第一次开红包金必须要激活
        $is_activite = UserActiviteCode::where("activite_user", $user->id)
            ->exists();
        if (!$is_activite) {
            throw new \Exception("must be activated first");
        }

        $this->buildRedpacketMoney($user->id);
        
        // 查询上级用户已开红包数量
        $pack_count = $this->where([
            "user_id" => $user->lv1_superior_id
        ])->whereNotNull("opened_at")
        ->count();

        // 查询上级用户已开红包金额
        $amount = UserReadpack::where("user_id", $user->lv1_superior_id)
            ->whereNotNull("opened_at")
            ->sum("amount");
        $target = setting("REDPACK_TARGET_AMOUNT", 100);

        // 如果上级用户有一个红包记录，则为其增加红包金
        if (
            ($pack_count > 0) && ($amount < $target)
        ) {
            $this->buildRedpacketMoney($user->lv1_superior_id);
        }

    }
    
    // 获得红包金
    protected function buildRedpacketMoney($user_id) {
        $user = User::where("id", $user_id)
                    ->lockForUpdate()
                    ->first();

        // 新建红包记录
        $pack = UserReadpack::create(["user_id" => $user->id]);

        // 打开红包
        $userRepository = app(UserRepository::class);

        $opened_count = $this->where("user_id", $user->id)
            ->whereNotNull("opened_at")
            ->count();

        // $points = [
        //     [60, 70],
        //     [10, 15],
        //     [5, 10],
        //     [1, 3],
        //     [0.5, 1],
        //     [0.1, 0.5],
        //     [0.01, 0.02],
        // ];

        $points_raw = setting("REDPACK_FREEZE_RANGES");
        if (empty($points_raw)) {
            throw new \Exception("redpacket points is empty");
        }

        $points = Str::of($points_raw)->split("(\r\n|\r|\n)")->map(function($item, $key){
            return explode(";", $item);
        })->toArray();

        $amount = $this->randomPackReward($points, $opened_count);

        DB::beginTransaction();
        try {
            // 添加红包金奖励
            $userRepository->addBalance([
                "user_id" => $user->id,
                "money" => $amount,
                "log_type" => 12,
                "balance_type" => "redpacket_balance",
            ]);
            // 标记红包已经打开

            $pack->opened_at = now();
            $pack->amount = $amount;

            $pack->save();

            DB::commit();
        }catch (\Throwable $th) {
            DB::rollBack();
            throw new \Exception("open redpacket error");
        }

        // 尝试提现红包金
        $this->withdrawalRedpacketBalance($user_id);
        
        return $pack;
    }

    // 随机生成红包奖励
    protected function randomPackReward($points, $count) {
        $user = auth()->user();
        $target = setting("REDPACK_TARGET_AMOUNT", 100);
        $goto_simple = false;
        // 计算已经获得金额
        $got_amount = UserReadpack::where("user_id", $user->id)
            ->sum("amount");
        if ($user->is_simple_redpack) {

            if ($count >=5 && $count < 8) {
                $goto_simple = boolval(random_int(0, 1));
            } elseif ($count == 8) {
                $goto_simple = true;
            } else {
                $goto_simple = false;
            }

            if (floatval($got_amount) >= $target ){
                $goto_simple = false;
            } 
        }

        if ($goto_simple) {
            // 简单模式
            return floatval($target) - floatval($got_amount);
        } else {
            // 普通模式
            if ($count >= count($points)) {
                $g = Arr::last($points);
            } else {
                $g = Arr::get($points, $count);
            }

            $start = $g[0] * 10;
            $end = $g[1] * 10;

            $r = random_int($start, $end);

            return floatval($r) / 10;
        }
        
    }

    // 提现红包金
    public function withdrawalRedpacketBalance($user_id) {
        $target = setting("REDPACK_TARGET_AMOUNT", 100);

        $user = User::where("id", $user_id)
                    ->lockForUpdate()
                    ->first();

        // 计算已经获得的红包金
        $got_amount = $user->redpacket_balance;
        if (floatval($got_amount) >= $target ){
            DB::beginTransaction();
            try {
                $user = User::where("id", $user->id)
                    ->lockForUpdate()
                    ->first();
                $user->redpacket_balance -= $target;
                $user->balance += $target;
                $user->save();
                DB::commit();
            }catch (\Throwable $th) {
                DB::rollBack();
                throw new \Exception("withdrawal redpacket balance error");
            }
        } 
    }
}
