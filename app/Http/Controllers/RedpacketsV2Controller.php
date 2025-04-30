<?php

namespace App\Http\Controllers;

use App\Repositories\RedpacketsV2Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RedpacketsV2Controller extends Controller
{

    protected function getRepositoryClass(){
        return RedpacketsV2Repository::class;
    }

    // 打开红包
    public function postOpen(Request $request) {
        $id = $request->input("id", null);

        if (empty($id)) {
            return $this->errorBadRequest("need red packet id");
        }

        $lock = Cache::lock('red_packet_v2' . $id, 2);

        $is_lock = false;
        do {
            $is_lock = $lock->get();
        } while (!$is_lock);

        $data = [];

        try {
            $data["amount"] = $this->repository->open($id);
            $info = $this->repository->getInfo($id);
            $data = array_merge($data, $info);
        } catch (\Throwable $th) {
            return $this->errorBadRequest($th->getMessage());
        } finally {
            $lock->release();
        }

        return $this->success($data);
        
    }

    // 查询所有红包
    public function getList(Request $request) {
        $result = $this->repository
            ->where("is_sale", 1)
            ->orderBy("sort", "desc")
            ->orderBy("created_at", "desc")
            ->paginate();
        return $this->success($result);
    }

    // 单个红包领取记录
    public function getLog(Request $request) {
        $id = $request->input("id", null);

        if (empty($id)) {
            return $this->errorBadRequest("need red packet id");
        }

        $result = $this->repository
            ->getLogList($id);
        return $this->success($result);
    }
}
