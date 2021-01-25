<?php


namespace App\Http\Controllers\API;


use App\Models\ROT\Qkey;
use Illuminate\Support\Facades\DB;

class QQROT_CHECK2
{
    /**
     * 验证群权限
     * @author Ehua(ehua999@163.com)
     * @date 2021/1/18 6:13
     */
    public static function auth_qun($qun)
    {
        //验证群权限
        if ($qun > 0) {
            $res = DB::table('q_auth_qun')->where('qun', $qun)->get();
            if ($res) {
                $data = json_encode($res, 256);
                $data = json_decode($data, true);
                return $data[0];
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * 验证qq权限
     * @author Ehua(ehua999@163.com)
     * @date 2021/1/18 6:13
     */
    public static function auth_qq()
    {

    }

    /**
     * 群邀请自动同意
     * @return bool
     * @author Ehua(ehua999@163.com)
     * @date 2021/1/18 6:16
     */
    public static function groupshare($key, $qq, $id)
    {

        if (preg_match("/邀请你加入群聊/", $key)) {
            preg_match("/groupcode=\"\d+\"/", $key, $requn);
            $qun = trim(str_replace("groupcode=\"", '', $requn[0]), '\"');

            preg_match("/msgseq=\"\d+\"/", $key, $requn);
            $seq = trim(str_replace("msgseq=\"", '', $requn[0]), '\"');
//            DB::table('log')->insert(['body'=>$qun,'body2'=>$qq,'body3'=>$seq]);

            $res = \QQROT\QQROT2::set_group_add_request($qun, $qq, $id, 1, 1, 'ok');
            return true;
        } else {
            return false;
        }
    }

    /**
     * 监控群消息自动转发
     * @author Ehua(ehua999@163.com)
     * @date 2021/1/18 6:44
     */
    public static function groupMsgshare($key, $qun, $qq, $die)
    {

        if (preg_match("/file,fileId/", $key)) {
            DB::table('log')->insert(['body' => '文件转发开始']);

            //查询群是否被监控
            $res = DB::table('q_share')->where('from_qun', 'like', '%' . $qun . '%')->get(['to_qun', 'key', 'run_bot']);
            $res = json_encode($res, 256);
            $res = json_decode($res, true);
            if (empty($res)) {
                DB::table('log')->insert(['body' => 5555555, 'body2' => $qun]);
                return false;
            } else {
                $res = $res[0];
            }

            preg_match("/\/.*-.*-.*-.*-.*,fileName/", $key, $requn);
            $fileId = trim(trim(str_replace("\/", '', $requn[0]), ',fileName'), '/');

            preg_match("/,fileName=.*?,fileSize/", $key, $requn);
            $fileName = trim(str_replace(",fileName=", '', $requn[0]), ',fileSize');

            if (DB::table('q_share_log')->where('file_name', $fileName)->count() == 0) {
                DB::table('q_share_log')->insert(['to' => $res['to_qun'], 'qun' => $qun, 'file_id' => $fileId, 'file_name' => $fileName, 'qq' => $qq]);

                \QQROT\QQROT2::group_file_to_group($qun, $res['to_qun'], $fileId);
            }
            if ($die == 'die') {
                die;
            }
        }
    }

    /**
     * 信息采集监控
     * @author Ehua(ehua999@163.com)
     * @date 2021/1/19 10:02
     */
    public static function monitoringMsg($key, $qun, $qq)
    {
        if (preg_match("/([A-Z]|[0-9]){5}-([A-Z]|[0-9]){4}/", $key, $onlykey)) {
            DB::table('made_csgo')->insert(['qun' => $qun, 'qq' => $qq, 'key' => $onlykey[0], 'intime' => date('Y-m-d H:i:s', time())]);
        }

    }

    /**
     * 菜单命令
     * @author Ehua(ehua999@163.com)
     * @date 2021/1/19 11:36
     */
    public static function groupMenu($key, $qun, $qunKey, $die)
    {
        $res = DB::table('q_system')->where('key', 'Menu')->value('value');
        if ($key == $res) {
            $res2 = DB::table('q_key')->whereIn('id', $qunKey)->get();
            $res2 = json_encode($res2, 256);
            $res2 = json_decode($res2, true);
            $str = "";
            foreach ($res2 as $k) {
                $str .= $k['key'] . "\n";
            }
            \QQROT\QQROT2::send_group_msg($qun, $str, $anonymous = false, 'str');//给群：12345 发消息
            if (!empty($res) && $die == 'die') {
                die;
            }
        }
    }

    /**
     * 加群同意
     * @param $key
     * @param $qun
     * @param $qq
     * @param $json
     * @param $data
     * @author Ehua(ehua999@163.com)
     * @date 2021/1/22 9:59
     */
    public static function groupadd($key, $qun, $qq, $json, $data)
    {
        if (empty($json)) {
            return;
        }
        $arr = json_decode($json, true);
        switch ($arr['type']) {
            case 'money':
                //{"type":"money","data":{"num":"0.01","qun":"146971736","qq":"3135491919","run":{"packet_qq":"50%","packet_qun":"50%"},"no":{"msg":"转账¥1 自动进群(备注群号)"}}}
                $id = $data['message']['id'];
                $money = $arr['data']['num'];
                $reason = $arr['data']['no']['msg'];
                if ($res = DB::table("q_auth_qun_add")->where(['qq' => $qq, 'qun' => $qun, 'status' => 1])->first()) {//已付费 自动通过进群
                    $res = \QQROT\QQROT2::set_group_add_request($qun, $qq, $id, 1, 3, '');
                } else {//未付费 拒绝
                    if (DB::table('q_auth_qun_add')->where(['qq' => $qq, 'qun' => $qun, 'money' => $money])->count() == 0) {
                        DB::table('q_auth_qun_add')->insert(['qq' => $qq, 'qun' => $qun, 'money' => $money, 'status' => 0, 'intime' => date('Y-m-d H:i:s', time())]);
                    }
                    $res = \QQROT\QQROT2::set_group_add_request($qun, $qq, $id, 2, 3, $reason);
                }
                break;
            case 'key':
                //{"type":"key","data":{"num":"5","run":{},"no":{"msg":"邀请码无效"}}}
                $id = $data['message']['id'];
                $num = $arr['data']['num'];
                $reason = $arr['data']['no']['msg'];

                if ($res = DB::table("q_auth_qun_add")->where(['qq' => $qq, 'qun' => $qun, 'status' => 1])->first()) {//已授权 自动通过进群
                    $res = \QQROT\QQROT2::set_group_add_request($qun, $qq, $id, 1, 3, '');
                } else {//未授权 拒绝
                    if(!preg_match("/([A-Z]|[0-9])+/", $key, $requn)){
                        $res = \QQROT\QQROT2::set_group_add_request($qun, $qq, $id, 2, 3, $reason);die;
                    }else{
                        $key=$requn[0];
                    }

                    $db_keys = DB::table('q_auth_qun_key')->where(['key' => $key, 'status' => 1])->first();
                    $db_keys = json_encode($db_keys, 256);
                    $db_keys = json_decode($db_keys, true);

                    if (empty($db_keys)) {
                        $res = \QQROT\QQROT2::set_group_add_request($qun, $qq, $id, 2, 3, $reason);
                    }
                    if($db_keys['use_num'] > $num){
                        $res = \QQROT\QQROT2::set_group_add_request($qun, $qq, $id, 2, 3, '该邀请码使用次数过多');
                    }

                    $newnum = $db_keys['use_num'] + 1;
                    DB::table('q_auth_qun_key')->where(['key' => $key])->update(['use_num' => $newnum]);
                    DB::table('q_auth_qun_add')->insert(['qq' => $qq, 'qun' => $qun, 'key' => $key, 'status' => 1, 'intime' => date('Y-m-d H:i:s', time())]);
                    $res = \QQROT\QQROT2::set_group_add_request($qun, $qq, $id, 1, 3, '');
                }
                break;
        }

    }

    /**
     * 好友转账确认
     * @param $key
     * @param $qun
     * @param $qq
     * @param $json
     * @param $data
     * @author Ehua(ehua999@163.com)
     * @date 2021/1/22 9:59
     */
    public
    static function groupadd2($key, $qq, $die)
    {
        if (preg_match("/[transfer,title=.*?元,memo=留言: .*?,transId=\d+]/", $key)) {
            preg_match("/title=.*?元/", $key, $requn);
            $money = trim(str_replace("title=", '', $requn[0]), '元');

            preg_match("/memo=留言: .*?,transId/", $key, $requn);
            $qun = trim(str_replace("memo=留言: ", '', $requn[0]), ',transId');
//            DB::table('log')->insert(['body'=>$qun,'body2'=>$qq,'body3'=>$seq]);
            if (empty($qun)) {
                $RES = \QQROT\QQROT2::send_friend_msg($qq, '请备注群号');
            }
            $res = DB::table("q_auth_qun_add")->where(['qq' => $qq, 'qun' => $qun, 'status' => 0])->first();
            $res = json_encode($res, 256);
            $res = json_decode($res, true);

            if ($res['money'] == $money) {
                DB::table("q_auth_qun_add")->where(['qq' => $qq, 'qun' => $qun, 'status' => 0])->update(['status' => 1]);
                $RES = \QQROT\QQROT2::send_friend_msg($qq, '授权成功，请重新加群：' . $qun);
            } else {
                $RES = \QQROT\QQROT2::send_friend_msg($qq, '金额不匹配：' . $res['money']);
            }
            if ($die = 'die') {
                die;
            }
        } else {
            return false;
        }


    }


    /**
     * 主人命令
     * @param $key
     * @param $quninfo
     * @param $qun
     * @param $qq
     * @param $die
     * @author Ehua(ehua999@163.com)
     * @date 2021/1/22 10:00
     */
    public
    static function boosCommand($key, $quninfo, $qun, $qq, $bossqq, $die)
    {
        if (!empty($quninfo)) {
            return;
        }
        if ($qq != $bossqq) {
            return;
        }
        switch ($key) {
            case "开启群":
                break;
            case "关闭群";
                break;
        }
    }


}
