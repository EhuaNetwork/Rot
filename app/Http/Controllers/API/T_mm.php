<?php


namespace App\Http\Controllers\API;


class T_mm
{
    static function mm($key,$qun)
    {
        if ($key == '营养快线') {
//            $pic = self::get();
//            $pic = \QQROT\QQROT2::upload_group_image($qun, $flash = false, $pic);
            $pic="功能暂时关闭";
            $res=\QQROT\QQROT2::send_group_msg($qun, $pic, $anonymous = false);
            die;
        }
    }
    static function get()
    {
        $res = get("https://api.uomg.com/api/rand.img3?sort=%E8%83%96%E6%AC%A1%E7%8C%AB&format=json");
        $res = stripcslashes($res);
        $res = json_decode($res, true);
        if (!empty($res['imgurl'])) {
            return $res['imgurl'];
        } else {
            return false;
        }
    }
}
