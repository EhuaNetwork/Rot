<?php


namespace App\Http\Controllers\API;


class T_MsgRot
{
    /**
     * 聊天机器人
     * @param $key
     * @author Ehua(ehua999@163.com)
     * @date 2021/1/29 18:52
     */
    static function MsgRot($key,$qun)
    {
        $qq = config('QQROT.qq');
        if (preg_match("/\[@$qq\]/", $key, $r)) {
            $keys = trim(str_replace("[@$qq]", '', $key));
            $data = [
                'question' => $keys,
                'limit' => 5,
                'api_key' => config('QQROT_MSG.api_key'),
                'api_secret' => config('QQROT_MSG.api_secret'),
                'type' => 'json',
            ];
            $res = post('http://i.itpk.cn/api.php', $data, []);

            \QQROT\QQROT2::send_group_msg($qun, $res, $anonymous = false, 'str');
            die;
        }
    }
}
