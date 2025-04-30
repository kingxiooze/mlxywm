<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Finance\Rate;

class FianceRateController extends Controller
{
    // 获取汇率
    public function index(Request $request) {
        $tcur = $request->input("tcur", "ZAR");
        $scur = $request->input("scur", "USD");

        $service = new Rate();
        $data = $service->get($scur, $tcur);

        return $this->success($data);
    }
}
