<?php

namespace App\Http\Controllers;

use App\Models\MoneyLog;
use Illuminate\Http\Request;

class ScrollController extends Controller
{
    // 获取滚动通知列表
    public function getList(Request $request) {
        $paginate = MoneyLog::whereIn("log_type", [1, 2])
            ->with("user:id,mobile")
            ->orderBy("created_at", "desc")
            ->paginate(10);
        return  $this->success($paginate);
    }
}
