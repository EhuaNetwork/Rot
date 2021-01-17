<?php


namespace App\Http\Controllers\API;


use App\Models\ROT\Qcfg;
use Illuminate\Support\Facades\DB;

class Task
{
    public function __construct()
    {
        \QQROT\QQROT::init( config('QQROT.qq'), config('QQROT.ip'),  config('QQROT.port'),  config('QQROT.pass'));
    }

    public function run(){
        $res=DB::table('q_timed task')->get();
        foreach ($res as $value){
            \QQROT\QQROT::sendGroupMsg($value->qun, $value->content, $anonymous = true, 'str');//给群：12345 发消息
            sleep(60*1);
        }
    }
}
