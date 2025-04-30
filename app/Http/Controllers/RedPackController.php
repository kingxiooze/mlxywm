<?php

namespace App\Http\Controllers;

use App\Models\MoneyLog;
use App\Models\User;
use App\Models\UserReadpack;
use Illuminate\Http\Request;
use App\Repositories\RedPackRepository;
use App\Repositories\UserRepository;


class RedPackController extends Controller
{

    protected function getRepositoryClass(){
        return RedPackRepository::class;
    }

    // 打开红包
    public function postOpen(Request $request) {
        try {
            $result = $this->repository->openPack();
        } catch (\Throwable $th) {
            return $this->errorBadRequest($th->getMessage());
        }
        

        return $this->success($result);
    }

    // 邀请红包记录
    public function getInvitePackLog(Request $request) {
        $logs = MoneyLog::where("user_id", auth()->id())
            ->where("log_type", 11)
            ->orderBy("created_at", "desc")
            ->with("user:id,name,avatar")
            ->with("sourceUser:id,name,avatar")
            ->get();
        return $this->success($logs);
    }

    // 消费金红包记录
    public function getFreezePackLog(Request $request) {
        $logs = MoneyLog::where("user_id", auth()->id())
            ->where("log_type", 12)
            ->orderBy("created_at", "desc")
            ->with("user:id,name,avatar")
            ->get();
        return $this->success($logs);
    }

    // 已领取的总金额
    public function getTotalAmount() {
        $user = auth()->user();

        $amount = UserReadpack::where("user_id", $user->id)
            ->whereNotNull("opened_at")
            ->sum("amount");
        $target = setting("REDPACK_TARGET_AMOUNT", 100);
        return $this->success([
            "amount" => $amount,
            "target" => $target,
        ]);
    }

    // 将红包金转换成任务金
    public function postConvertToMission(Request $request) {
        $user = auth()->user();

        // 邀请人数
        $invite_count = User::where("lv1_superior_id", $user->id)->count();

        // 不满3人时无法领取
        if ($invite_count < 3) {
            return $this->errorBadRequest("three people must be invited");
        }

        // 只能领取一次
        $is_received = MoneyLog::where("user_id", $user->id)
            ->where("log_type", 17)
            ->exists();
        if ($is_received) {
            return $this->errorBadRequest("can only be receive once");
        }

        $amount = $user->redpacket_balance;

        $userRepository = app(UserRepository::class);

        // 减少用户红包金
        $userRepository->addBalance([
            "user_id" => $user->id,
            "money" => 0 - $amount,
            "log_type" => 17,
            "balance_type" => "redpacket_balance"
        ]);
        // 增加用户任务金
        $userRepository->addBalance([
            "user_id" => $user->id,
            "money" => $amount,
            "log_type" => 17,
            "balance_type" => "mission_balance"
        ]);

        $user->refresh();

        return $this->success([
            "redpacket_balance" => $user->redpacket_balance,
            "mission_balance" => $user->mission_balance,
        ]);
    }
}
