<?php

namespace App\Http\Controllers;

use App\Repositories\InviteRewardRepository;
use Illuminate\Http\Request;


class InviteRewardController extends Controller
{

    protected function getRepositoryClass(){
        return InviteRewardRepository::class;
    }

    // 领取邀请奖励
    public function postReceive(Request $request) {
       // return $this->errorBadRequest("close");
        $setting_id = $request->input("setting_id", null);

        if (empty($setting_id)) {
            return $this->errorBadRequest("设置错误");
        }

        try {
            $result = $this->repository->receive($setting_id);
        } catch (\Throwable $th) {
            return $this->errorBadRequest($th->getMessage());
        }

        return $this->success($result);
    }
    
    // 查询所有设置
    public function getList(Request $request) {
        $type = $request->input("type", 1);
        $result = $this->repository
            ->where("type",$type)
            ->orderBy("limit", "asc")
            ->paginate();
        return $this->success($result);
    }
}
