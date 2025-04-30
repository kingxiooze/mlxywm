<?php

namespace App\Http\Controllers;

use App\Models\PrizeWheelLog;
use App\Models\PrizeWheelReward;
use App\Repositories\PrizeWheelRepository;
use Illuminate\Http\Request;


class PrizeWheelController extends Controller
{

    protected function getRepositoryClass(){
        return PrizeWheelRepository::class;
    }

    // 抽奖
    public function postTurn(Request $request) {
        $user = auth()->user();
        try {
            $result = $this->repository->doTurn($user->id);
            $result->load([
                "coupon:id,discount,expire_time", 
                "item:id,name,image"
            ]);
        } catch (\Throwable $th) {
            return $this->errorBadRequest($th->getMessage());
        }
        
        return $this->success($result);
    }

    // 奖品列表
    public function getRewardList(Request $request) {
        $rewards = PrizeWheelReward::with([
            "coupon:id,discount,expire_time", 
            "item:id,name,image"
        ])->orderBy("created_at", "desc")->get();
        return $this->success($rewards);
    }

    // 我抽中记录
    public function getMyLog(Request $request) {
        $user = auth()->user();
        $paginate = PrizeWheelLog::with([
            "coupon:id,discount,expire_time", 
            "item:id,name,image"
        ])->where("user_id", $user->id)
        ->orderBy("created_at", "desc")
        ->paginate(10);
        return $this->success($paginate);
    }
}
