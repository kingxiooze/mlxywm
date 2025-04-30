<?php

namespace App\Http\Controllers;

use App\Repositories\CustomerServiceRepository;
use Illuminate\Http\Request;

class CustomerServiceController extends Controller
{
    protected function getRepositoryClass(){
        return CustomerServiceRepository::class;
    }

    // 查询所有客服
    public function getList(Request $request) {
        $salesman_code = $request->input("salesman_code", null);
        $service_type = $request->input("service_type", null);

        $query = $this->repository
            ->orderBy("created_at", "desc");
        if ($salesman_code) {
            $query = $query->where("salesman_code", $salesman_code);
        }

        if ($service_type) {
            $query = $query->where("service_type", $service_type);
        }

        $result = $query->paginate();
        return $this->success($result);
    }
}
