<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserIsBan
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!empty($user->baned_at)) {
            $data = [
                "status" => "error",
                "code" => 403,
                "message" => "your account is baned.",
                "data" => [],
                "error" => []
            ];
            return response()->json($data);
        }
        return $next($request);
    }
}
