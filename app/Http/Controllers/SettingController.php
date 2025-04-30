<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function getInfo(Request $request) {
        $key = $request->input("key", null);
        if (empty($key)) {
            return $this->errorNotFound();
        }

        $record = Setting::where("key", $key)->first();

        return $this->success($record);
    }
}
