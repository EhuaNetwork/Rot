<?php


namespace App\Http\Controllers\API;


class T_Qbang
{
    static function Qbang($key,$qun)
    {
        if (preg_match("/XQ\d+/", $key, $qq)) {

            $qq = str_replace('XQ', '', $qq[0]);
            $str = self::get($qq);
            \QQROT\QQROT2::send_group_msg($qun, $str, $anonymous = false, 'str');
            die;
        }
    }


    static function get($qq)
    {
        $res = get("http://api.qb-api.com/qb-api.php?mod=cha&qq=$qq");
        $res = stripcslashes($res);
        $res = str_replace("\n", '', $res);
        $res = str_replace(" ", '', $res);
        $res = json_decode($res, true);
        if (!empty($res['data']['mobile'])) {
            return $res['data']['mobile'];
        } else {
            return '未查到';
        }
    }
}
