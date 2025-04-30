<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Models\PrizeWheelLog;
use App\Models\PrizeWheelReward;
use App\Models\User;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Class PrizeWheelRepository.
 *
 * @package namespace App\Repositories;
 */
class PrizeWheelRepository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return PrizeWheelLog::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function doTurn($user_id) {
        $lock = Cache::lock("PRIZE_WHEEL_DO_TURN:" . $user_id, 10);

        DB::beginTransaction();
        try {
            $lock->block(5);

            $user = User::find($user_id);
            // 检查抽奖次数
            if ($user->prize_count <= 0) {
                throw new \Exception("the number of prize wheel has been exhausted");
            }
            // 扣除抽奖次数
            $user->prize_count -= 1;
            $user->save();


            $rewards = PrizeWheelReward::all();
            $prize_arr = [];
            foreach ($rewards as $reward) {
                $prize_arr[$reward->id] = $reward->rate * 100;
            }

            $reward_id = $this->randomPrize($prize_arr);
            
            $reward = $rewards->find($reward_id);

            $reward->distributeReward($user_id);

            PrizeWheelLog::create([
                "user_id" => $user_id,
                "reward_id" => $reward->id,
                "name" => $reward->name,
                "reward_type" => $reward->reward_type,
                "coupon_id" => $reward->coupon_id,
                "item_id" => $reward->item_id,
                "cash_amount" => $reward->cash_amount
            ]);

            DB::commit();

            return $reward;

        } catch (LockTimeoutException $ex){
            DB::rollBack();
            throw new \Exception("duplicate request, retry again please.");
        } catch (\Throwable $th) {
            DB::rollBack();
            throw new \Exception($th->getMessage());
        } finally {
            $lock?->release();
        }
    }

    protected function randomPrize($proArr) {
        $result = '';
        //概率数组的总概率精度
        $proSum = array_sum($proArr);
        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($proArr);
        return $result;
    }
    
}
