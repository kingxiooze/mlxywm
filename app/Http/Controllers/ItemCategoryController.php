<?php

namespace App\Http\Controllers;

use App\Repositories\ItemCategoryRepository;
use Illuminate\Http\Request;


class ItemCategoryController extends Controller
{

    protected function getRepositoryClass(){
        return ItemCategoryRepository::class;
    }

    // 查询所有商品类型
    public function getList(Request $request) {
        $result = $this->repository
            ->orderBy("created_at", "desc")
            ->paginate();
        return $this->success($result);
    }
}
