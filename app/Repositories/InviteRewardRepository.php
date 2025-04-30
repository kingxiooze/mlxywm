<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Models\User;
use App\Models\InviteReward;
use App\Models\InviteRewardLog;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Class InviteRewardRepository.
 *
 * @package namespace App\Repositories;
 */
class InviteRewardRepository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return InviteReward::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    // 领取邀请奖励
    public function receive($setting_id) {
        $user = auth()->user();

        // 查询是否已经领取
        $is_received = InviteRewardLog::where("user_id", $user->id)
            ->where("setting_id", $setting_id)
            ->exists();
        if ($is_received) {
            throw new \Exception("你已经领取了该奖励");
            //throw new \Exception("Você já recebeu a recompensa");
            
            
        }

        // 查询未领取的奖项
        $record = InviteReward::where("id", $setting_id)
            ->first();

        if (empty($record)) {
            throw new \Exception("setting nout found");
        }

        // 总邀请人数
        $total_invite = $user->total_invite;
        // 20230822: 还有这个接口也是一样，必须要下级购买才可以领取邀请奖励收益
        // 计算有购买过商品的邀请用户数量
        $ownitem_invite = User::where("lv1_superior_id", $user->id)
            ->has("own_item", ">", "0")
            ->count();
        // 如果购买过商品的邀请用户数量比总邀请人数少，则按照购买过商品的邀请用户数量来计算
        // 不考虑更多的情况，因为更多时，必定数据出现了问题
        if ($ownitem_invite < $total_invite) {
            $total_invite = $ownitem_invite;
        }

        if ($record->limit > $total_invite) {
            throw new \Exception("你没有达到邀请数量要求");
             //throw new \Exception("Você não atendeu ao número de convites necessários");
        }

        $userRepository = app(UserRepository::class);
        
        $lock = Cache::lock("INVITE_CASHBACK_RECEIVE:" . $user->id, 10);
        DB::beginTransaction();
        try {
            $lock->block(5);
            // 用户添加记录
            InviteRewardLog::create([
                "user_id" => $user->id,
                "setting_id" => $record->id,
                "reward" => $record->rewards
            ]);
            // 用户添加余额
            $userRepository->addBalance([
                "user_id" => $user->id,
                "money" => $record->rewards,
                "log_type" => 7,
                "balance_type" => "balance"
            ]);
            DB::commit();
        } catch (LockTimeoutException $e) {
            throw new \Exception("duplicate request, retry again please.");
             //throw new \Exception("Pedido duplicado, tente novamente, por favor.");
        } catch (\Throwable $th) {
            report($th);
            throw new \Exception("receive error");
           // throw new \Exception("Incapaz de reivindicar");
        } finally {
            DB::rollBack();
            $lock?->release();
        }

        return [
            "reward" => $record->rewards
        ];
    }
    
}
