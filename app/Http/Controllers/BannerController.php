<?php

namespace App\Http\Controllers;

use App\Repositories\BannerRepository;
use Illuminate\Http\Request;


class BannerController extends Controller
{

    protected function getRepositoryClass(){
        return BannerRepository::class;
    }

    // 查询所有Banner
    public function getList(Request $request) {
        $result = $this->repository
            ->orderBy("created_at", "desc")
            ->paginate();
        return $this->success($result);
    }
}
