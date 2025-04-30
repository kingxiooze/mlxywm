<?php

namespace App\Http\Controllers;

use App\Models\UserYoutubeLink;
use Illuminate\Http\Request;

class YoutubeLinkController extends Controller
{
    // 所有审核通过的列表
    public function getList(Request $request) {
        $paginate = UserYoutubeLink::where("status", 1)
            ->with("user:id,name,avatar")
            ->orderBy("look_num", "desc")
            ->paginate(20);
        return $this->success($paginate);
    }

    // 添加接口
    public function postNew(Request $request) {
        $data = $request->validate([
            'name' => "required", 
            'link' => "required", 
            'image' => "required"
        ]);
         $link = $request->input("link", null);
        //验证链接是否重复
         $linkitem = UserYoutubeLink::where("link", $link)
          ->first();
        
        if(!empty($linkitem)){
          return $this->errorBadRequest("The submitted youbute link already exists");  
        }
        $user = auth()->user();
        
        $data["user_id"] = $user->id;
        
        $paginate = UserYoutubeLink::where("user_id", $user->id)
                    ->orderBy("created_at", "desc")
                    ->first();
      
        if(!empty($paginate)){
            //状态为通过就可以继续创建
            if($paginate->status == 0){
               return $this->errorBadRequest("You have submitted it, please wait for review");   
            }
           //UserYoutubeLink::create($data); 
        }
         //添加数据
         UserYoutubeLink::create($data); 
        return $this->ok();
    }

    // 我的提交列表接口
    public function getMyList(Request $request) {
        $user = auth()->user();

        $paginate = UserYoutubeLink::orderBy("created_at", "desc")
            ->where("user_id", $user->id)
            ->paginate(20);
        return $this->success($paginate);
    }
}
