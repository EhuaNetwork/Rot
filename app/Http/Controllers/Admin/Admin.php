<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class Admin extends Controller
{
    public function index(){



        $token=Session::get('qqrot');


        return view('Admin.index');
    }
}
