<?php


namespace App\Http\Controllers\API;


use Illuminate\Support\Facades\DB;

class QQROT_CHECK
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
    public static function groupshare($key, $qq)
    {

        if (preg_match("/.*邀请你加入群聊.*/", $key)) {
            preg_match("/groupcode=\"\d+\"/", $key, $requn);
            $qun = trim(str_replace("groupcode=\"", '', $requn[0]), '\"');

            preg_match("/msgseq=\"\d+\"/", $key, $requn);
            $seq = trim(str_replace("msgseq=\"", '', $requn[0]), '\"');
//            DB::table('log')->insert(['body'=>$qun,'body2'=>$qq,'body3'=>$seq]);
            \QQROT\QQROT::setGroupAddRequest($qun, $qq, $seq, 11, 1);
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
    public static function groupMsgshare($key, $qun, $die)
    {

        if (preg_match("/.*?_file,fileId.*?/", $key)) {
            DB::table('log')->insert(['body' => '文件转发开始']);

            //查询群是否被监控
            $res = DB::table('q_share')->where('from_qun', 'like', '%' . $qun . '%')->get(['to_qun', 'key']);
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

            DB::table('log')->insert(['body' => '文件转发完毕']);

            \QQROT\QQROT::forwardFile(0, $qun, $res['to_qun'], $fileId, $fileName);

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
    public static function groupMenu($key, $qun,$qunKey, $die)
    {
        $res = DB::table('q_system')->where('key', 'Menu')->value('value');
        if ($key == $res) {
            $res2 = DB::table('q_key')->whereIn('id', $qunKey)->get();
            $res2 = json_encode($res2, 256);
            $res2 = json_decode($res2, true);
            $str = "";
            foreach ($res2 as $k) {
                $str .= $k['key'] ."\n";
            }
            \QQROT\QQROT::sendGroupMsg($qun, $str, $anonymous = false, 'str');//给群：12345 发消息
            if (!empty($res) && $die == 'die') {
                die;
            }
        }
    }
}
