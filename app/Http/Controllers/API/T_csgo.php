<?php


namespace App\Http\Controllers\API;


use Illuminate\Support\Facades\DB;

class T_csgo
{
    static function csgokey()
    {
        $time = time() - 60 * 5;
        $res = DB::table('made_csgo')
            ->where('intime', '>', date('Y-m-d H:i:s', $time))
            ->limit(20)
            ->orderBy('intime', 'desc')->get();
        $res = json_encode($res, 256);
        $res = json_decode($res, true);

        $str = "";
        foreach ($res as $k) {
            $str .= $k['qq'] . '--' . $k['key'] . "\n";
        }
        if ($str == '') {
            $str = "暂无";
        } else {
            $str = "近5分钟内的开黑信息\n" . $str;
        }
        return $str;
    }

    static function csgopub($key,$qun)
    {
        if ($key == '在线开黑') {
            $str =T_csgo::csgokey();;
            \QQROT\QQROT2::send_group_msg($qun, $str, $anonymous = false, 'str');
            die;
        }
    }
}
