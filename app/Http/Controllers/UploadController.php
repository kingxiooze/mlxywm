<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    // 上传图片到本地
    public function local(Request $request) {
        if (!$request->hasFile('file')) {
            return $this->errorBadRequest("file not found");
        }

        if (!$request->file('file')->isValid()) {
            return $this->errorBadRequest("file not valid");
        }

        return redirect()->action([UploadController::class, "oss"], [], 307);

        $path = $request->file("file")->store('files', 'public');

        $url = Storage::disk("public")->url($path);
        return $this->success([
            "url" => $url
        ]);
    }

    // 上传图片到OSS
    public function oss(Request $request) {
        // if (true) {
        //     return $this->errorBadRequest("Upload failed. Please refresh the page multiple times and try again");
        // }

        if (!$request->hasFile('file')) {
            return $this->errorBadRequest("file not found");
        }

        if (!$request->file('file')->isValid()) {
            return $this->errorBadRequest("file not valid");
        }

        $path = $request->file("file")->store('files', 'oss');

        $url = Storage::disk("oss")->url($path);
        return $this->success([
            "url" => $url
        ]);
    }
}
