<?php

namespace App\Repositories;

use App\Models\MoneyLog;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Models\SignLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


/**
 * Class SignLogRepository.
 *
 * @package namespace App\Repositories;
 */
class SignLogRepository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return SignLog::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
    public function signIn($user_id) {
        $log = SignLog::where("user_id", $user_id)
            ->orderBy("signed_at", "desc")
            ->first();
        $today = Carbon::now()->setTime(0, 0);
        $yestday = $today->copy()->subDay();
        $last = (new Carbon($log->signed_at ?? 0))->setTime(0, 0);

        // 如果最后一次签到在今天
        if ($last->diffInDays($today) == 0) {
            throw new \Exception("Already checked in today");
        }

        $is_continue = false;

        // 如果最后一次签到在昨天
        if ($last->diffInDays($yestday) == 0) {
            // 可以判断为连续签到
            $is_continue = true;
        }

        // 	每日签到获得X金
        $signDailyReward = (float) setting('SIGN_DAILY_REWARD');
        // 第二天签到递增X金
        $signContinusAdd = (int) setting('SIGN_CONTINUS_ADD');
        // 	签到第几天不再递增
        $signMaxAddDays = (int) setting('SIGN_MAX_ADD_DAYS');
        // 连续签到X天数可获得一定金
        $signContinuousRewardDays = (int) setting('SIGN_CONTINUOUS_REWARD_DAYS');
        // 连续签到一定天数可获得X金
        $signContinuousRewardCoin = (float) setting('SIGN_CONTINUOUS_REWARD_COIN');

        // 是否为连续签到
        if ($is_continue) {
            // 计算连续签到多少天
            $max_sign = $log->duration_day + 1;
            // 如果连续签到天数大于最大递增天数，则不递增
            if ($max_sign >= $signMaxAddDays) {
                $reward = $signDailyReward + $signContinusAdd * ($signMaxAddDays - 1);
            } else {
                $reward = $signDailyReward + $signContinusAdd * ($max_sign - 1);
            }

            // 连续签到X天可额外获得X金
            if ($max_sign % $signContinuousRewardDays == 0) {
                $reward += $signContinuousRewardCoin;
            }

        } else {
            $reward = $signDailyReward;
            $max_sign = 1;
        }

        
        DB::beginTransaction();
        try {
            // 添加签到记录
            $todayLog = $this->create([
                "user_id" => $user_id,
                "reward" => $reward,
                "signed_at" => now(),
                "duration_day" => $max_sign,
            ]);

            $userRepository = app(UserRepository::class);
            $userRepository->addBalance([
                "user_id" => $user_id,
                "money" => $reward,
                "log_type" => 8
            ]);
            
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw new \Exception("sign error");
        }
        

        return $todayLog;

    }
}
