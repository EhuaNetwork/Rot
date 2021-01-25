<?php


namespace App\Http\Controllers\API;


use App\Models\ROT\Qcfg;
use Illuminate\Support\Facades\DB;

class Task
{
    public function __construct()
    {
        \QQROT\QQROT2::init(config('QQROT.qq'), config('QQROT.ip'), (config('QQROT.port')+1), config('QQROT.pass'));

    }
    //定时广告
    public function guanggao(){
        $res=DB::table('q_timed task')->get();
        foreach ($res as $value){
            \QQROT\QQROT2::send_group_msg($value->qun, $value->content, $anonymous = true);//给群：12345 发消息
            sleep(60*1);
        }
    }

    //群文件定时归档
    public function check_file($qun='146971736')
    {
        $fromnum = $qun;
        $path = '/';
        $res = \QQROT\QQROT2::get_file_list($fromnum, $path);//获取群目录里的文件
        foreach ($res as $k) {
            $bool=0;
            if ($k['type'] == 1) {
                $bool=1;
                $topath = $this->check_file_fileType($fromnum, $k['name'], $path);
                $res = \QQROT\QQROT2::move_group_file($fromnum, $k['id'], $path, $topath);
            }

        }
        if($bool!=1){
            echo  'success';die;
        }else{
            echo "<script>window.location.href='http://rot.ehua.pro/api/check_file'</script>";
        }
    }
    private function check_file_fileType($qun, $file_name, $path)
    {
        $path = 'temp';//todo
        $res = DB::table('q_qunfile_cfg')->where(['qun' => $qun])->first();
        if (empty($res)) {
            return $path;
        }
        $res = json_encode($res, 256);
        $res = json_decode($res, true);
        $cfg = json_decode($res['cfg'], true);
        return $this->check_file_fileTypeeach($file_name, $cfg, $path);
    }
    private function check_file_fileTypeeach($file_name, $cfg, $path)
    {
        if (empty($cfg)) {
            return $path;
        }
        foreach ($cfg as $k => $y) {
            if (preg_match($y, $file_name)) {
                return $k;
            }
        }
        return $path;
    }





}
