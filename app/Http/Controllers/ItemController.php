<?php

namespace App\Http\Controllers;

use App\Models\ItemPriceAuditLog;
use App\Repositories\ItemRepository;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    protected function getRepositoryClass(){
        return ItemRepository::class;
    }

    // 商品列表
    public function getList(Request $request) {
        // 最低价格
        $min_price = $request->input("min_price", null);
        // 最高价格
        $max_price = $request->input("max_price", null);
        // 是否拼团
        $is_group = $request->input("is_group", null);
        // 名称模糊搜索
        $search_name = $request->input("search_name", null);
        // 类型ID
        $category_id = $request->input("category_id", 1);
        //  $isVip = $request->input("vip", null);
        //  if($isVip==1){
        //      $category_id = 2;
        //  }
        $where = [
            "is_sell" => true
        ];
        if($min_price !== null) {
            array_push($where, ["price", ">=", $min_price]);
        }

        if($max_price !== null) {
            array_push($where, ["price", "<=", $max_price]);
        }

        if($is_group !== null) {

            array_push($where, ["is_group_purchase", "=", boolval($is_group)]);
        }

        if($search_name !== null) {
            array_push($where, ["name", "like", "%$search_name%"]);
        }

        // if($category_id !== null) {
        //     array_push($where, ["category_id", "=", $category_id]);
        // }
        

        $result = $this->repository
            ->with("category")
            ->where($where)
            ->orderBy("sort", "asc")
            ->paginate(10);

        return $this->success($result);
    }

    // 商品详情
    public function getDetail(Request $request) {
        $item_id = $request->input("id", 0);
        $result = $this->repository
            ->with('category')
            ->find($item_id);
       
        return $this->success($result);
    }

    // 购买商品
    public function postBuy(Request $request) {
        $item_id = $request->input("id", null);
        $upitem_id = $request->input("upitem_id", null);
        if (empty($item_id)) {
            return $this->errorNotFound();
        }

        $user = auth()->user();
        // 检查交易密码
        // try {
        //     $user->checkTradePassword();
        // } catch (\Throwable $th) {
        //     return $this->errorBadRequest($th->getMessage());
        // }

        try {
            $buyAmount = $this->repository->purchase(
                $item_id, 
                $user->id,
                $upitem_id
            );
        } catch (\Throwable $th) {
            return $this->errorBadRequest($th->getMessage());
        }
        return $this->success([
            "buy_amount" => intval($buyAmount)
        ]);
    }

    // 售卖商品
    public function postSell(Request $request) {
        $item_id = $request->input("id", null);
        if (empty($item_id)) {
            return $this->errorNotFound();
        }

        $user = auth()->user();
        // 检查交易密码
        try {
            $user->checkTradePassword();
        } catch (\Throwable $th) {
            return $this->errorBadRequest($th->getMessage());
        }

        try {
            $data = $this->repository->sale(
                $item_id, 
                $user->id
            );
        } catch (\Throwable $th) {
            return $this->errorBadRequest($th->getMessage());
        }
        return $this->success($data);
    }

    // 获取商品价格记录
    public function getItemPriceLog(Request $request) {
        $item_id = $request->input("id", null);
        if (empty($item_id)) {
            return $this->errorNotFound();
        }

        $logs = ItemPriceAuditLog::where("item_id", $item_id)
            ->orderBy("created_at", "desc")
            ->paginate(10);
        return $this->success($logs);
    }

    // 领取商品收益
    public function postGainItemEarning(Request $request) {
        $user_item_id = $request->input("user_item_id", null);
        if (empty($user_item_id)) {
            return $this->errorBadRequest("empty user item");
        }

        try {
            $this->repository->gainItemEarning(
                $user_item_id
            );
        } catch (\Throwable $th) {
            return $this->errorBadRequest($th->getMessage());
        }

        return $this->ok();
    }
}
