<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class Login extends Controller
{
    //登录页面
    public function index()
    {

        return view('Admin.login');
    }

    //登录页面
    public function var_login()
    {
        $key = \request()->key;
        if (!\request()->ajax()) {
            return redirect('');
        }

        if ($KEY=DB::table('v_token')->where('token', $key)->first()) {
            Session::put('qqrot',(array)$KEY);
            return response()->json(['code' => 200, 'msg' => '登录成功', 'data' => '']);
       } else {
            return response()->json(['code' => 404, 'msg' => '密钥失效', 'data' => '']);
        }
    }
}
