<?php


namespace App\Http\Controllers\API;


use Illuminate\Support\Facades\DB;

class Demo
{
    public function init(){
        \QQROT\QQROT::init(config('QQROT.qq'), config('QQROT.ip'), config('QQROT.port'), config('QQROT.pass'));

        $res=\QQROT\QQROT::getGroupList();
        dd($res);die;
    }
}

