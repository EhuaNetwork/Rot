<?php


namespace App\Http\Controllers\API;


use Illuminate\Support\Facades\DB;

class Demo
{
    public function init()
    {
        echo "<pre\>";
        $res = '';
        \QQROT\QQROT2::init(config('QQROT.qq'), config('QQROT.ip'), (config('QQROT.port') + 1), config('QQROT.pass'));

//        $res=\QQROT\QQROT2::sendGroupTempMsg('146971736','测试内容',false);
//        $res=\QQROT\QQROT2::send_friend_msg('3135491919','测试内容');
//        $res=\QQROT\QQROT2::forwardFile('511476741','146971736','50da8bde-5a55-11eb-8723-5452007b7f04');

//        $res=\QQROT\QQROT2::send_group_hb(2,3,0.1,'146971736','测试红包','150638','','','','');

//        $res = $this->check_file();
//        $res = \QQROT\QQROT2::move_group_file(146971736, '338b2bf8-5ba2-11eb-8ba5-5452007bd6c0','/', '视频');

        $res=$this->get_group();
//        $this->get_file('529250621', 'v4');
        dd($res);
        die;

    }

    /**
     * 偷文件
     * @param $qun
     * @param $path
     * @author Ehua(ehua999@163.com)
     * @date 2021/1/24 20:01
     */
    public function get_file($qun, $path)
    {
        $fromnum = $qun;
        $tonum = 146971736;
        $res = \QQROT\QQROT2::get_file_list($fromnum, $path);
        foreach ($res as $k) {
            if ($k['type'] == 1) {
                if (DB::table('q_share_log')->where('file_name', $k['name'])->count() == 0) {
                    DB::table('q_share_log')->insert(['to' => $tonum, 'qun' => $qun, 'file_id' => trim($k['id'],'\/'), 'file_name' => $k['name'], 'qq' => 1963806765]);
                    $res = \QQROT\QQROT2::group_file_to_group($fromnum, $tonum, $k['id']);
                }
                var_dump($k['name']);
            }
        }
        dd($res);
    }

    /**
     * 群号列表
     * @author Ehua(ehua999@163.com)
     * @date 2021/1/24 20:01
     */
    public function get_group()
    {
        $res = \QQROT\QQROT2::get_group_list();
        $str = '';
        foreach ($res as $k) {
            if ($k['id'] != '') {
                $str .= ',' . $k['id'];

            }
        }
        dd($str);
    }

    /**
     * 生成邀请码
     * @author Ehua(ehua999@163.com)
     * @date 2021/1/24 20:00
     */
    function check_key()
    {
        for ($i = 5; $i > 0; $i--) {
            $res = $this->random_code(5);
            DB::table('q_auth_qun_key')->insert(['key' => $res]);
        }
    }

    function random_code($length = 8, $chars = null)
    {
        if (empty($chars)) {
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        }

        $count = strlen($chars) - 1;
        $code = '';
        while (strlen($code) < $length) {
            $code .= substr($chars, rand(0, $count), 1);
        }
        return $code;
    }
}

