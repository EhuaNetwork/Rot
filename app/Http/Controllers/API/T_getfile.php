<?php


namespace App\Http\Controllers\API;


use Illuminate\Support\Facades\DB;

class  T_getfile
{

    /**
     * 偷文件
     * @param $qun
     * @param $path
     * @author Ehua(ehua999@163.com)
     * @date 2021/1/24 20:01
     */
    static function get_file($qun, $to, $path)
    {
        $fromnum = $qun;
        $tonum = $to;

        $res = \QQROT\QQROT2::get_file_list($fromnum, $path);

        foreach ($res as $k) {
            if ($k['type'] == 1) {
                //查询群是否被监控
                $res = DB::table('q_share')->where('to_qun', $to)->first(['to_qun', 'file_cfg', 'run_bot', 'join_qun']);
                $res = json_encode($res, 256);
                $res = json_decode($res, true);
                $fileName = $k['name'];
                $fileId=trim('/',$k['id']);
                if (empty($res['join_qun'])) {
                    $count = DB::table('q_share_log')->where('file_name', $fileName)->where('to_qun', $res['to_qun'])->count();
                } else {
                    $sql = "select count(*) from q_share_log where ";

                    $to = $res['to_qun'];
                    $sql = $sql . "file_name='$fileName' and (to_qun='$to'";
                    $join_qun = $res['join_qun'];
                    if (empty($join_qun)) {
                        $sql = $sql . ')';
                    } else {
                        $sql = $sql . " or to_qun in ($join_qun) )";
                    }
                    $count = DB::select($sql);
                    $count = json_encode($count, 256);
                    $count = json_decode($count, true);
                    $count = $count[0]['count(*)'];
                }
                if ($count == 0) {
                    $r = \QQROT\QQROT2::group_file_to_group($qun, $res['to_qun'], $k['id']);
                    if ($r) {
                        DB::table('q_share_log')->insert(['to_qun' => $res['to_qun'], 'qun' => $qun, 'file_id' => $fileId, 'file_name' => $fileName, 'qq' => $k['uploader']['id'], 'intime' => date('Y-m-d H:i:s', time())]);
                    }
                }
            }
        }
    }
}
