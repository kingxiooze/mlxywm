<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Models\Coupon;
use App\Models\UserCoupon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Arr;

/**
 * Class CouponRepository.
 *
 * @package namespace App\Repositories;
 */
class CouponRepository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Coupon::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    // 领取优惠券
    public function receive() {
        $user = auth()->user();

        if ($user->is_coupon_received) {
            throw new \Exception("You have already received the coupon");
        }

        $received_coupon = null;

        $lock = Cache::lock("COUPON_RECEIVE:" . $user->id, 10);

        DB::beginTransaction();
        try {
            $lock->block(5);

            $stack = [];

            $coupons = Coupon::all();
            foreach ($coupons as $coupon) {
                for ($i=0; $i < $coupon->weights; $i++) { 
                    array_push($stack, $coupon->id);
                }
            }

            $randomly = Arr::shuffle($stack);
            $result_index = Arr::random($randomly);
            $result = $randomly[$result_index];

            $received_coupon = Coupon::find($result);

            UserCoupon::create([
                "user_id" => $user->id,
                "coupon_id" => $received_coupon->id,
                "status" => 0,
                "expire_at" => now()->addHours($received_coupon->expire_time)
            ]);

            $user->is_coupon_received = 1;
            $user->save();

            DB::commit();
        } catch (LockTimeoutException $e) {
            DB::rollBack();
            throw new \Exception("duplicate request, retry again please.");
        } catch (\Throwable $th) {
            DB::rollBack();
            throw new \Exception($th->getMessage());
        } finally {
            $lock?->release();
        }

        return $received_coupon;
    }
    
}
