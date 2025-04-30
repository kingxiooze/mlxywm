<?php

namespace App\Http\Controllers;

use App\Jobs\ItemEarningJob;
use App\Models\ReviewRecord;
use Illuminate\Http\Request;
use App\Models\ReviewTmpl;
use App\Models\UserItem;
use Illuminate\Database\Eloquent\Builder;

class ReviewController extends Controller
{
    // 随机评价模板
    public function getRandomTmpl(Request $request) {
        $item_id = $request->input("item_id", null);
        if (empty($item_id)) {
            return $this->errorBadRequest("choose item first");
        }
        $tmpl = ReviewTmpl::where("item_id", $item_id)
            ->inRandomOrder()
            ->first();
        return $this->success($tmpl);
    }

    // 评价列表
    public function getList(Request $request) {
        $item_id = $request->input("item_id", null);
        if (empty($item_id)) {
            return $this->errorBadRequest("choose item first");
        }

        $list = ReviewRecord::whereHas("user_item", function(Builder $query) use ($item_id){
            $query->where('item_id', $item_id);
        })->with(["tmpl"])
        ->orderBy("review_records.created_at", "desc")
        ->paginate(10);

        return $this->success($list);
    }

    // 发表评价
    public function postNew(Request $request) {
        $user_item_id = $request->input("user_item_id", null);
        $tmpl_id = $request->input("tmpl_id", null);
        $image = $request->input("image", null);
        $content = $request->input("content", null);

        if (empty($user_item_id)) {
            return $this->errorBadRequest("choose item first");
        }

        if (empty($tmpl_id)) {
            // 2023-05-23: 新增评论手动上传资料
            $tmpl_id = 0;
            // return $this->errorBadRequest("choose review first");
        }

        $user_item = UserItem::where("id", $user_item_id)->first();
        if (empty($user_item)) {
            return $this->errorNotFound("item not exists");
        }

        if ($user_item->status == 1) {
            return $this->errorNotFound("item already review");
        }

        // 如果当前时间没有超过设置的物流周期，则不允许评论
        $obtain_time = $user_item->created_at
            ->addHours($user_item->item->logistics_hours);
        if (now() < $obtain_time) {
            return $this->errorBadRequest("item in logistics");
        }

        ReviewRecord::create([
            "user_item_id" => $user_item_id,
            "tmpl_id" => $tmpl_id,
            "user_id" => auth()->id(),
            "image" => $image,
            "content" => $content,
        ]);
        
        $user_item->status = 1;
        $user_item->save();

        // ItemEarningJob::dispatch($user_item_id);

        return $this->ok();
    }
}
