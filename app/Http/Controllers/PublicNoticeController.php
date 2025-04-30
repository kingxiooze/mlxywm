<?php

namespace App\Http\Controllers;

use App\Repositories\PublicNoticeRepository;
use Illuminate\Http\Request;

class PublicNoticeController extends Controller
{
    protected function getRepositoryClass(){
        return PublicNoticeRepository::class;
    }

    // 获取最新的弹出公告
    public function getNewest() {
        $result = $this->repository
            ->orderBy("created_at", "desc")
            ->first();
        return $this->success($result);
    }
}
