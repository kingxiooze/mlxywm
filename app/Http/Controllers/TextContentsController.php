<?php

namespace App\Http\Controllers;

use App\Models\TextContent;
use Illuminate\Http\Request;

class TextContentController extends Controller
{
    // 获取列表
    public function getList(Request $request) {
        $types = $request->input("type", null);

        $query = TextContent::orderBy("sort", "asc");
        if ($types != null) {
            $query = $query->where("types", $types);
        }
        $list = $query->get();
        return $this->success($list);
    }
}
