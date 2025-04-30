<?php

namespace App\Repositories;

use App\Models\SignLog;
use App\Models\User;
use App\Models\UserCashback;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Models\UserWithdrawal;

/**
 * Class WithdrawalRepository.
 *
 * @package namespace App\Repositories;
 */
class WithdrawalRepository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserWithdrawal::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    // 资产审核-邀请用户
    public function checkStateByInvite($user) {
        $invite_count = User::where("parent_code", $user->code)
            ->count();
        $max_count = setting("REWARD_INVITE_USER_COUNT", 50);
        $peruser_reward = setting("INVITE_USER_REWARD", 3);

        if ($invite_count > $max_count) {
            $invite_count = $max_count;
        }

        return $peruser_reward * $invite_count;
    }

    // 资产审核-返现奖励
    public function checkStateByCashback($user) {
        return UserCashback::where("user_id", $user->id)->sum("back_amount");
    }
    
    // 资产审核-签到奖励
    public function checkStateBySign($user) {
        return SignLog::where("user_id", $user->id)->sum("reward");
    }

}
