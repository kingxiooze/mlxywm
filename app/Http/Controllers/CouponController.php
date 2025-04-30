<?php

namespace App\Http\Controllers;

use App\Models\UserCoupon;
use App\Repositories\CouponRepository;
use Illuminate\Http\Request;


class CouponController extends Controller
{

    protected function getRepositoryClass(){
        return CouponRepository::class;
    }

    // 领取优惠券
    public function postReceive(Request $request) {
        try {
            $result = $this->repository->receive();
        } catch (\Throwable $th) {
            return $this->errorBadRequest($th->getMessage());
        }

        return $this->success($result);
    }

    // 我的优惠券列表
    public function getMyCouponList(Request $request) {
        $user = auth()->user();
        $paginate = UserCoupon::where("user_id", $user->id)
            ->where("status", 0)
            ->with("coupon")
            ->orderBy("created_at", "desc")
            ->paginate(10);

        return $this->success($paginate);
    }
}
