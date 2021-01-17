<?php

namespace App\Http\Middleware\Video;

use Closure;
use Illuminate\Support\Facades\DB;

class VideoVerify
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(empty($request->token)){
            return response()->json(['code'=>201,'msg'=>'拒绝请求','data'=>'Token cannot be null']);
        }
        $res=DB::table('v_token')->where(['token'=>$request->token])->first();
        if(empty($res)){
            return response()->json(['code'=>500,'msg'=>'拒绝请求','data'=>'Token does not pass verification']);
        }

        if (empty(\request()->key)  || empty(\request()->qq)) {
            return response()->json(['code' => 201, 'msg' => '参数不足', 'data' => 'key qun qq cannot be null']);
        }
        return $next($request);
    }
}
