<?php

namespace QQROT;
///* 使用示例 */
//$robot = array(
//    'qq' => '1963806765', //机器人QQ号码
//    'ip' => '47.93.232.49', //接口IP
//    'port' => '10429', //接口端口
//    'pass' => '', //密码
//);
//QQROT::init($robot['qq'], $robot['ip'], $robot['port'], $robot['pass']); //初始化
//
//QQROT::sendPrivateMsg(12345, 'hello word!');//给QQ：12345 发好友消息


class QQROT2
{
    static $logonqq = '';
    static $getway = '';
    static $pass = '';

    /**
     * 初始化机器人
     * @param number $logonqq 机器人QQ号码
     * @param string $ip 机器人框架IP
     * @param number $port 机器人框架端口
     * @param string $pass 密码，可不填
     */
    static function init($logonqq, $ip, $port, $pass = '')
    {
        self::$logonqq = $logonqq;
        self::$getway = 'http://' . $ip . ':' . $port;
        self::$pass = $pass;
    }

    /**
     * 发送群消息
     * @param number $togroup 指定群号
     * @param string $text 指定消息内容
     * @param bool $anonymous 是否匿名(true,false)
     * @return mixed
     * @author Ehua(ehua999@163.com)
     * @date 2021/1/19 21:35
     */
    static function send_group_msg($togroup, $text, $anonymous = false)
    {
        $postData = [
            'bot' => self::$logonqq,
            'group' => $togroup,
            'anonymous' => $anonymous,
            'msg' => $text
        ];

        $result = self::sendRequest('send_group_msg', $postData);
        return self::parseResult($result);
    }

    /**
     * 发送好友消息
     * @param number $toqq 指定qq
     * @param string $text 指定消息内容
     * @return mixed
     * @author Ehua(ehua999@163.com)
     * @date 2021/1/19 21:35
     */
    static function send_friend_msg($toqq, $text)
    {
        $postData = [
            'bot' => self::$logonqq,
            'qq' => $toqq,
            'msg' => $text
        ];
        $result = self::sendRequest('send_friend_msg', $postData);

        return self::parseResult($result);
    }


    /**
     * 群文件转发至群
     * @param number $fromnum 指定群
     * @param string $tonum 到群
     * @return mixed
     * @author Ehua(ehua999@163.com)
     * @date 2021/1/19 21:35
     */
    static function group_file_to_group($fromnum, $tonum, $fileid)
    {
        $postData = [
            'bot' => self::$logonqq,
            'from' => $fromnum,
            'to' => $tonum,
            'fileid' => $fileid,
        ];
        $result = self::sendRequest('group_file_to_group', $postData);
        return self::parseResult($result);
    }


    /**
     * 好友拉群同意
     * @param $group
     * @param $qq
     * @param $handle
     * @param $event
     * @param $reason
     * @return bool|mixed
     * @author Ehua(ehua999@163.com)
     * @date 2021/1/19 23:51
     */
    static function set_group_add_request($group, $qq, $id,$handle, $event, $reason)
    {
        $postData = [
            'bot' => self::$logonqq,
            'group' => $group,
            'qq' => $qq,
            'id' => $id,
            'handle' => $handle,
            'event' => $event,
            'reason' => $reason,
        ];
        $result = self::sendRequest('set_group_add_request', $postData);
        return self::parseResult($result);
    }
    static function send_group_hb($type,$num,$money,$group,$text,$pass,$id,$skin,$qq,$average){
        $postData = [
            'bot' => self::$logonqq,
            'type' => $type,
            'num' => $num,
            'money' => $money,
            'group' => $group,
            'text' => $text,
            'pass' => $pass,
            'id' => $id,
            'skin' => $skin,
            'qq' => $qq,
            'average' => $average,
        ];
        $result = self::sendRequest('send_group_hb', $postData);
        dd($result);
        return self::parseResult($result);
    }

    /**
     * 取群文件列表
     * @param $group
     * @param $folder
     * @return bool|mixed
     * @author Ehua(ehua999@163.com)
     * @date 2021/1/20 1:44
     */
    static function get_file_list($group, $folder)
    {
        $postData = [
            'bot' => self::$logonqq,
            'group' => $group,
            'folder' => $folder,
        ];
        $result = self::sendRequest('get_file_list', $postData);
        return self::parseResult($result);
    }

    /**
     * 获取群列表
     * @return bool|mixed
     * @author Ehua(ehua999@163.com)
     * @date 2021/1/21 13:16
     */
    static function get_group_list()
    {
        $postData = [
            'bot' => self::$logonqq,
        ];
        $result = self::sendRequest('get_group_list', $postData);
        $result = str_replace("0\x11\x10", '', $result);
        return self::parseResult($result);
    }

    /**
     * 移动文件
     * @param $qun
     * @param $fileId
     * @param $formath
     * @param $topath
     * @return bool|mixed
     * @author Ehua(ehua999@163.com)
     * @date 2021/1/21 13:18
     */
    static function move_group_file($qun,$fileId,$formath,$topath){
        $postData = [
            'bot' => self::$logonqq,
            'group' => $qun,
            'fileid' => $fileId,
            'from' => $formath,
            'to' =>$topath,
        ];
        $result = self::sendRequest('move_group_file', $postData);
        return self::parseResult($result);
    }


















    static function parseResult($result)
    {
        $data = json_decode($result, 1);
        if ($data['code'] == 0) {
            if (isset($data['data'])) {
                return $data['data'];
            }
            return true;
        } else {
            return false;
        }
    }


    static function sendRequest($func, $data, $header = [])
    {
        $url = self:: $getway . '/' . $func;
        return self::curl_post($url, $data, $header);
    }

    static function curl_post($url, $data, $header)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $sResult = curl_exec($ch);
        if ($sError = curl_error($ch)) {
            die($sError);
        }
        curl_close($ch);
        return $sResult;
    }


}
