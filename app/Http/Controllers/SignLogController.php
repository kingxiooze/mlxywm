<?php

namespace App\Http\Controllers;

use App\Repositories\SignLogRepository;
use Illuminate\Http\Request;

class SignLogController extends Controller
{
    protected function getRepositoryClass(){
        return SignLogRepository::class;
    }

    // 获取签到记录
    public function getLog(Request $request) {
        $year = $request->input("year", date("Y"));
        $month = $request->input("month", date("m"));

        $result = $this->repository
            ->orderBy("signed_at", "asc")
            ->findWhere([
                "user_id" => auth()->id(),
                ['signed_at', "YEAR", $year],
                ['signed_at', "MONTH", $month],
            ]);

        return $this->success($result);
    }

    // 签到
    public function postSignIn(Request $request) {
        try {
            $result = $this->repository->signIn(
                auth()->id()
            );
        } catch (\Throwable $th) {
            return $this->errorBadRequest($th->getMessage());
        }
        
        return $this->success($result);
    }
}
