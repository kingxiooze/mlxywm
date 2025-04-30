<?php

namespace App\Http\Controllers;

use App\Repositories\NewsRepository;
use Illuminate\Http\Request;


class NewsController extends Controller
{

    protected function getRepositoryClass(){
        return NewsRepository::class;
    }

    // 查询新闻
    public function getList(Request $request) {
        $item_category_id = $request->input("item_category_id", null);
        $query = $this->repository
            ->orderBy("created_at", "desc");
        if (!empty($item_category_id)) {
            $query = $query->where("item_category_id", $item_category_id);
        }

        $result = $query->paginate();

        return $this->success($result);
    }
}
