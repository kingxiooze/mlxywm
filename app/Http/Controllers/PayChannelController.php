<?php

namespace App\Http\Controllers;

use App\Repositories\PayChannelRepository;
use Illuminate\Http\Request;


class PayChannelController extends Controller
{

    protected function getRepositoryClass(){
        return PayChannelRepository::class;
    }

    // 查询所有PayChannel
    public function getList(Request $request) {
        $result = $this->repository
            ->whereNull("hidden_at")
            ->orderBy("sort", "asc")
            ->get();
        return $this->success($result);
    }
}
