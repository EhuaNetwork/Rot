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


class QQROT
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
        self::$pass = $pass ? 'pass=' . md5($pass) : '';
    }

    /**
     * 申请会话(缓冲区)
     * @return number 指定会话(缓冲区)id
     */
    static function allocSession()
    {
        $result = self::sendRequest('allocsession', '');
        $data = json_decode($result, true);
        return $data['session_id'];
    }

    /**
     * 删除会话(缓冲区)
     * @param number $sessid 指定会话(缓冲区)id
     * @return string 返回状态，OK即为返回成功，其他则为报错信息
     */
    static function removeSession($sessid)
    {
        $result = self::sendRequest('removesession', array('sessid' => $sessid));
        $data = json_decode($result, true);
        return $data['status'];
    }

    /**
     * 清空事件缓冲区
     * @param number $sessid 指定会话(缓冲区)id
     * @return string 返回状态，OK即为返回成功，其他则为报错信息
     */
    static function resetEvent($sessid)
    {
        $result = self::sendRequest('resetevent', array('sessid' => $sessid));
        $data = json_decode($result, true);
        return $data['status'];
    }

    /**
     * 获取并清空事件缓冲区
     * @param number $sessid 指定会话(缓冲区)id，不填则根据上报信息获取
     * @return string 返回事件数组
     */
    static function getEvent($sessid = 0)
    {
        if ($sessid) {
            $result = self::sendRequest('geteventv2', array('sessid' => $sessid));
            $data = json_decode($result, 1);
            $events = $data['events'];
        } else {
            $getData = $GLOBALS['HTTP_RAW_POST_DATA'] ? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents('php://input');
            $events = array(json_decode($getData, 1));
        }
        return $events;
    }

    /**
     * 发送好友消息
     * @param number $toqq 指定好友QQ
     * @param string $text 指定消息内容
     * @param string $type 消息类型，留空为text，其他可填内容：json,xml
     * @return string 返回事件数组
     */
    static function sendPrivateMsg($toqq, $text, $type = '')
    {
        if ($type == 'str') {
            $type = '';
        }

        $result = self::sendRequest('sendprivate' . $type . 'msg', array('fromqq' => self::$logonqq, 'toqq' => $toqq, ($type ? $type : 'text') => $text));
        return self::parseResult($result);
    }

    /**
     * 撤回私聊消息
     * @param number $toqq 指定好友QQ
     * @param string $random 发送消息返回的random
     * @param string $req 发送消息返回的req
     * @return bool 是否成功撤回
     */
    static function deletePrivateMsg($toqq, $random, $req)
    {
        $postData = array('fromqq' => self::$logonqq, 'toqq' => $toqq, 'random' => $random, 'req' => $req);

        $result = self::sendRequest('deleteprivatemsg', $postData);
        $data = json_decode($result, 1);
        return $data['ret'];
    }

    /**
     * 撤回群聊消息
     * @param number $togroup 指定群号
     * @param string $random 发送消息返回的random
     * @param string $req 发送消息返回的req
     * @return bool 是否成功撤回
     */
    static function deleteGroupMsg($togroup, $random, $req)
    {
        $postData = array('fromqq' => self::$logonqq, 'group' => $togroup, 'random' => $random, 'req' => $req,);

        $result = self::sendRequest('deletegroupmsg', $postData);
        $data = json_decode($result, 1);
        return $data['ret'];
    }

    /**
     * 发送群消息
     * @param number $togroup 指定群号
     * @param string $text 指定消息内容
     * @param bool $anonymous 是否匿名(true,false)
     * @param string $type 消息类型，留空为text，其他可填内容：json,xml
     * @return string 返回事件数组
     */
    static function sendGroupMsg($togroup, $text, $anonymous = false, $type = '')
    {
        if ($type == 'str') {
            $type = '';
        }

        $postData = array('fromqq' => self::$logonqq, 'togroup' => $togroup, ($type ? $type : 'text') => $text, 'anonymous' => $anonymous);
        $result = self::sendRequest('sendgroup' . $type . 'msg', $postData);
        return self::parseResult($result);
    }


    /**
     * 发送群消息
     * @param number $togroup 指定群号
     * @param string $text 指定消息内容
     * @param bool $anonymous 是否匿名(true,false)
     * @return string 返回事件数组
     */
    static function sendGroupTempMsg($togroup, $toqq, $text)
    {
        $postData = array('fromqq' => self::$logonqq, 'togroup' => $togroup, 'text' => $text, 'toqq' => $toqq);
        $result = self::sendRequest('sendgrouptempmsg', $postData);
        return self::parseResult($result);
    }


    /**
     * 添加好友
     * @param number $toqq 指定对方QQ
     * @param string $text 指定附言
     * @return string 返回事件数组
     */
    static function addFriend($toqq, $text)
    {
        $postData = array('fromqq' => self::$logonqq, 'toqq' => $toqq, 'text' => $text);
        $result = self::sendRequest('addfriend', $postData);
        return self::parseResult($result);
    }

    /**
     * 添加群
     * @param number $togroup 指定群号
     * @param string $text 指定附言
     * @return string 返回事件数组
     */
    static function addGroup($togroup, $text)
    {
        $postData = array('fromqq' => self::$logonqq, 'togroup' => $togroup, 'text' => $text);
        $result = self::sendRequest('addgroup', $postData);
        return self::parseResult($result);
    }

    /**
     * 删除好友
     * @param number $toqq 指定对方QQ
     * @return string 返回事件数组
     */
    static function deleteFriend($toqq)
    {
        $postData = array('fromqq' => self::$logonqq, 'toqq' => $toqq);
        $result = self::sendRequest('deletefriend', $postData);
        return self::parseResult($result);
    }

    /**
     * 设置特别关心好友
     * @param number $toqq 指定对方QQ
     * @param bool $care 指定是否关心(true,false)
     * @return string 返回事件数组
     */
    static function setFriendCare($toqq, $care)
    {
        $postData = array('fromqq' => self::$logonqq, 'toqq' => $toqq, 'care' => $care);
        $result = self::sendRequest('setfriendcare', $postData);
        return self::parseResult($result);
    }

    /**
     * 设置屏蔽好友
     * @param number $toqq 指定对方QQ
     * @param bool $ignore 指定是否屏蔽(true,false)
     * @return string 返回事件数组
     */
    static function setFrienDignMsg($toqq, $ignore)
    {
        $postData = array('fromqq' => self::$logonqq, 'toqq' => $toqq, 'ignore' => $ignore);
        $result = self::sendRequest('setfriendignmsg', $postData);
        return self::parseResult($result);
    }

    /**
     * 上传图片
     * @param number $tonum 指定对方QQ或QQ群号码
     * @param number $fromtype 指定图片来源类型(0:pic参数,1:本地文件,2:网络文件 默认为0)
     * @param string $type 上传图片类型(private:好友图片,group:QQ群)
     * @param string $source 不同图片类型传输不同值
     * @param bool $flashpic 指定是否闪照(true,false)
     * @return string 返回事件数组
     */
    static function sendPic($tonum, $type = 'group', $fromtype = 0, $source, $flashpic)
    {
        $type = strtolower($type);
        $postData = array('fromqq' => self::$logonqq, ($type == 'group' ? 'togroup' : 'toqq') => $tonum, 'flashpic' => $flashpic, 'fromtype' => $fromtype);
        $sourceNames = array(
            'pic', //urlencode(base64_encode(文件流)))
            'path', //指定文件路径(请使用绝对路径,存在特殊字符请使用URL编码)
            'url' //指定文件url(存在特殊字符请使用URL编码)
        );
        $postData[$sourceNames[$fromtype]] = $source;
        $result = self::sendRequest('send' . $type . 'pic', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }

    /**
     * 发送语音
     * @param number $tonum 指定对方QQ或QQ群号码
     * @param number $fromtype 指定语音来源类型(0:pic参数,1:本地文件,2:网络文件 默认为0)
     * @param string $audiotype 指定语音类型(0普通语音,1变声语音,2文字语音,3红包匹配语音)
     * @param string $type 上传图片类型(private:好友图片,group:QQ群)
     * @param string $source 不同图片类型传输不同值
     * @param string $text 指定语音文字
     * @return string 返回事件数组
     */
    static function sendAudio($tonum, $type = 'group', $audiotype = 0, $fromtype = 0, $source, $text)
    {
        $type = strtolower($type);
        $postData = array('fromqq' => self::$logonqq, ($type == 'group' ? 'togroup' : 'toqq') => $tonum, 'type' => $audiotype, 'text' => $text, 'fromtype' => $fromtype);
        $sourceNames = array(
            'audio', //urlencode(base64_encode(文件流)))
            'path', //指定文件路径(请使用绝对路径,存在特殊字符请使用URL编码)
            'url' //指定文件url(存在特殊字符请使用URL编码)
        );
        $postData[$sourceNames[$fromtype]] = $source;
        $result = self::sendRequest('send' . $type . 'pic', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }

    /**
     * 上传头像
     * @param number $fromtype 指定图片来源类型(0:pic参数,1:本地文件,2:网络文件 默认为0)
     * @param string $source 不同图片类型传输不同值
     * @return string 返回事件数组
     */
    static function uploadFacePic($fromtype = 0, $source)
    {
        $postData = array('fromqq' => self::$logonqq, 'fromtype' => $fromtype);
        $sourceNames = array(
            'audio', //urlencode(base64_encode(文件流)))
            'path', //指定文件路径(请使用绝对路径,存在特殊字符请使用URL编码)
            'url' //指定文件url(存在特殊字符请使用URL编码)
        );
        $postData[$sourceNames[$fromtype]] = $source;
        $result = self::sendRequest('uploadfacepic', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }

    /**
     * 上传群头像
     * @param number $togroup 指定群号
     * @param number $fromtype 指定图片来源类型(0:pic参数,1:本地文件,2:网络文件 默认为0)
     * @param string $source 不同图片类型传输不同值
     * @return string 返回事件数组
     */
    static function uploadGroupFacePic($togroup, $fromtype = 0, $source)
    {
        $postData = array('fromqq' => self::$logonqq, 'togroup' => $togroup, 'fromtype' => $fromtype);
        $sourceNames = array(
            'audio', //urlencode(base64_encode(文件流)))
            'path', //指定文件路径(请使用绝对路径,存在特殊字符请使用URL编码)
            'url' //指定文件url(存在特殊字符请使用URL编码)
        );
        $postData[$sourceNames[$fromtype]] = $source;
        $result = self::sendRequest('uploadgroupfacepic', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }

    /**
     * 设置群名片
     * @param number $togroup 指定群号
     * @param number $toqq 指定群成员QQ
     * @param string $card 指定群名片(存在特殊字符请使用URL编码)
     * @return string 返回事件数组
     */
    static function setGroupCard($togroup, $toqq, $card)
    {
        $postData = array('fromqq' => self::$logonqq, 'togroup' => $togroup, 'toqq' => $toqq, 'card' => $card);
        $result = self::sendRequest('setgroupcard', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }

    /**
     * 获取昵称
     * @param number $toqq 指定对方QQ
     * @param bool $fromcache 指定是否使用缓存(true,false)
     * @return string 返回事件数组
     */
    static function getNickname($toqq, $fromcache)
    {
        $postData = array('fromqq' => self::$logonqq, 'toqq' => $toqq, 'fromcache' => $fromcache);
        $result = self::sendRequest('getnickname', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }

    /**
     * 从缓存取群名称
     * @param number $group 指定群号
     * @return string 返回事件数组
     */
    static function getGroupName($togroup)
    {
        $postData = array('group' => $togroup);
        $result = self::sendRequest('getgroupnamefromcache', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }

    /**
     * 取好友列表
     * @return string 返回事件数组
     */
    static function getFriendList()
    {
        $postData = array('logonqq' => self::$logonqq);
        $result = self::sendRequest('getfriendlist', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }

    /**
     * 取群列表
     * @return string 返回事件数组
     */
    static function getGroupList()
    {
        $postData = array('logonqq' => self::$logonqq);
        $result = self::sendRequest('getgrouplist', $postData);
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 取群成员列表
     * @param number $group 指定群号
     * @return string 返回事件数组
     */
    static function getGroupMemberList($togroup)
    {
        $postData = array('logonqq' => self::$logonqq, 'group' => $togroup);
        $result = self::sendRequest('getgroupmemberlist', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }


    /**
     * 设置管理员
     * @param number $group 指定群号
     * @param number $toqq 指定对方QQ
     * @param bool $bemgr 是否成为管理员(true,false)
     * @return string 返回事件数组
     */
    static function setGroupManger($togroup, $toqq, $bemgr)
    {
        $postData = array('fromqq' => self::$logonqq, 'group' => $togroup, 'toqq' => $toqq, 'bemgr' => $bemgr);
        $result = self::sendRequest('setgroupmgr', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }

    /**
     * 取管理层列表
     * @param number $group 指定群号
     * @return string 返回事件数组
     */
    static function getGroupMangerList($togroup)
    {
        $postData = array('fromqq' => self::$logonqq, 'group' => $togroup);
        $result = self::sendRequest('getgroupmgrlist', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }

    /**
     * 取群名片
     * @param number $togroup 指定群号
     * @param number $toqq 指定对方QQ
     * @return string 返回事件数组
     */
    static function getGroupCard($togroup, $toqq)
    {
        $postData = array('fromqq' => self::$logonqq, 'group' => $togroup, 'toqq' => $toqq);
        $result = self::sendRequest('getgroupcard', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }

    /**
     * 取个性签名
     * @param number $toqq 指定对方QQ
     * @return string 返回事件数组
     */
    static function getMemberSignature($toqq)
    {
        $postData = array('fromqq' => self::$logonqq, 'group' => $togroup, 'toqq' => $toqq);
        $result = self::sendRequest('getsignat', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }

    /**
     * 设置昵称
     * @param string $nickname 昵称
     * @return string 返回事件数组
     */
    static function setNickname($nickname)
    {
        $postData = array('fromqq' => self::$logonqq, 'nickname' => $nickname);
        $result = self::sendRequest('setnickname', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }

    /**
     * 设置个性签名
     * @param string $signature 指定个性签名
     * @return string 返回事件数组
     */
    static function setMemberSignature($signature)
    {
        $postData = array('fromqq' => self::$logonqq, 'signature' => $signature);
        $result = self::sendRequest('setsignat', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }


    /**
     * 移出群成员
     * @param number $togroup 指定群号
     * @param number $toqq 指定对方QQ
     * @param bool $ignoreaddgrequest 拒绝再加群申请(true,false)
     * @return string 返回事件数组
     */
    static function kickGroupMember($togroup, $toqq, $ignoreaddgrequest = true)
    {
        $postData = array('fromqq' => self::$logonqq, 'group' => $togroup, 'toqq' => $toqq, 'ignoreaddgrequest' => $ignoreaddgrequest);
        $result = self::sendRequest('kickgroupmember', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }

    /**
     * 禁言群成员
     * @param number $togroup 指定群号
     * @param number $toqq 指定对方QQ
     * @param number $time 指定禁言时长(以秒计)
     * @return string 返回事件数组
     */
    static function muteGroupMember($togroup, $toqq, $time)
    {
        $postData = array('fromqq' => self::$logonqq, 'group' => $togroup, 'toqq' => $toqq, 'time' => $time);
        $result = self::sendRequest('mutegroupmember', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }


    /**
     * 退群
     * @param number $togroup 指定群号
     * @return string 返回事件数组
     */
    static function exitGroup($togroup)
    {
        $postData = array('fromqq' => self::$logonqq, 'group' => $togroup);
        $result = self::sendRequest('exitgroup', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }

    /**
     * 解散群
     * @param number $togroup 指定群号
     * @return string 返回事件数组
     */
    static function disGroup($togroup)
    {
        $postData = array('fromqq' => self::$logonqq, 'group' => $togroup);
        $result = self::sendRequest('dispgroup', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }

    /**
     * 全员禁言
     * @param number $togroup 指定群号
     * @param bool $ismute 指定是否禁言(true,false)
     * @return string 返回事件数组
     */
    static function setGroupWholeMute($togroup, $ismute = true)
    {
        $postData = array('fromqq' => self::$logonqq, 'group' => $togroup, 'ismute' => $ismute);
        $result = self::sendRequest('setgroupwholemute', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }

    /**
     * 置群员权限
     * @param number $togroup 指定群号
     * @param string $privname 权限（发起新的群聊:newgroup;发起临时会话:newtempsession;上传文件:uploadfile;上传相册:uploadphotoalbum;邀请他人加群:invitein;匿名聊天:anonymous;坦白说:tanbaishuo;新成员查看历史消息:newmembercanviewhistorymsg;邀请方式:inviteway）
     * @param bool $ismute 指定是否禁言(true,false)，当$privname等于inviteway时，该字段为：way 指定方式(1.无需审核;2.需要管理员审核;3.100人以内无需审核)
     * @return string 返回事件数组
     */
    static function setGroupPriv($togroup, $privname, $allow = true)
    {
        $postData = array('fromqq' => self::$logonqq, 'togroup' => $togroup, 'allow' => $allow);
        if ($privname == 'inviteway') {
            unset($postData['allow']);
            $postData['way'] = $allow;
        }
        $result = self::sendRequest('setgrouppriv_' . strtolower($privname), $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }


    /**
     * 设置位置共享
     * @param number $togroup 指定群号
     * @param string $posx 指定经度
     * @param string $posy 指定纬度
     * @param bool $enable 指定是否开启(true,false)
     * @return string 返回事件数组
     */
    static function setSharePositon($togroup, $posx, $posy, $enable = true)
    {
        $postData = array('fromqq' => self::$logonqq, 'group' => $togroup, 'posx' => $posx, 'posy' => $posy, 'enable' => $enable);
        $result = self::sendRequest('setsharepos', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }

    /**
     * 上报当前位置
     * @param number $togroup 指定群号
     * @param string $posx 指定经度
     * @param string $posy 指定纬度
     * @return string 返回事件数组
     */
    static function uploadPosition($togroup, $posx, $posy)
    {
        $postData = array('fromqq' => self::$logonqq, 'group' => $togroup, 'posx' => $posx, 'posy' => $posy);
        $result = self::sendRequest('uploadpos', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }


    /**
     * 取禁言时间
     * @param number $togroup 指定群号
     * @return string 返回事件数组
     */
    static function getMuteTime($togroup)
    {
        $postData = array('fromqq' => self::$logonqq, 'group' => $togroup);
        $result = self::sendRequest('getmutetime', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }

    /**
     * 处理群验证事件
     * @param number $togroup 指定群号
     * @param number $toqq 指定来源QQ
     * @param string $seq 指定seq
     * @param number $op 指定处理类型(11同意 12拒绝  14忽略)
     * @param number $type 指定事件类型(群事件_某人申请加群:3 群事件_我被邀请加入群:1))
     * @return string 返回事件数组
     */
    static function setGroupAddRequest($togroup, $toqq, $seq, $op, $type)
    {
        $postData = array('fromqq' => self::$logonqq, 'group' => $togroup, 'qq' => $toqq, 'seq' => $seq, 'op' => $op, 'type' => $type);
        $result = self::sendRequest('setgroupaddrequest', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }

    /**
     * 处理好友验证事件
     * @param number $toqq 指定来源QQ
     * @param string $seq 指定seq
     * @param number $op 指定处理类型(1同意 2拒绝)
     * @return string 返回事件数组
     */
    static function setFriendAddrRquest($toqq, $seq, $op)
    {
        $postData = array('fromqq' => self::$logonqq, 'qq' => $toqq, 'seq' => $seq, 'op' => $op);
        $result = self::sendRequest('setfriendaddrequest', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }


    /**
     * 上传文件
     * 注意:本命令会先返回当前HTTP请求 后执行功能
     * @param number $togroup 指定群号
     * @param string $path 指定文件名(存在特殊字符请使用URL编码)
     * @return string 返回事件数组
     */
    static function uploadFile($togroup, $path)
    {
        $postData = array('fromqq' => self::$logonqq, 'group' => $togroup, 'path' => $path);
        $result = self::sendRequest('uploadfile', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }

    /**
     * 创建群文件夹
     * @param number $togroup 指定群号
     * @param string $folder 指定文件夹名称(存在特殊字符请使用URL编码)
     * @return string 返回事件数组
     */
    static function newGroupFolder($togroup, $folder)
    {
        $postData = array('fromqq' => self::$logonqq, 'group' => $togroup, 'folder' => $folder);
        $result = self::sendRequest('newgroupfolder', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }

    /**
     * 设置在线状态
     * @param number $state 指定在线主状态(11在线 31离开 41隐身 50忙碌 60Q我吧 70请勿打扰)
     * @param number $sun [当state=11时可选]sun 指定在线子状态1(0普通在线 1000我的电量 1011信号弱 1024在线学习 1025在家旅游 1027TiMi中 1016睡觉中 1017游戏中 1018学习中 1019吃饭中 1021煲剧中 1022度假中 1032熬夜中)
     * @param number $power [当sun=1000时可选]power 自动电量(取值1到100)
     * @return string 返回事件数组
     */
    static function setOnlineState($state, $sun, $power)
    {
        $postData = array('fromqq' => self::$logonqq, 'state' => $state);
        if ($state == 11) {
            $postData['sun'] = $sun;
            if ($sun == 1000) {
                $postData['power'] = $power;
            }
        }
        $result = self::sendRequest('setonlinestate', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }

    /**
     * 发送名片赞
     * @param number $toqq 指定对方QQ
     * @return string 返回事件数组
     */
    static function sendLike($toqq)
    {
        $postData = array('fromqq' => self::$logonqq, 'toqq' => $toqq);
        $result = self::sendRequest('sendlike', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }

    /**
     * 取图片下载地址
     * @param string $photo 指定图片代码(存在特殊字符请使用URL编码)
     * @param string $togroup [群聊图片必填，私聊图片不填]指定群号
     * @return string 返回事件数组
     */
    static function getPhotoUrl($photo, $togroup)
    {
        $postData = array('fromqq' => self::$logonqq, 'photo' => $photo, 'group' => $togroup);
        $result = self::sendRequest('getphotourl', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }


    /**
     * 文件转发
     * @param number $type 转发类型，0:群转群；1:群转好友；2:好友转好友
     * @param number $fromnum 指定来源群或QQ
     * @param string $tonum 指定目标QQ或群
     * @param string $fileid 指定文件ID(存在特殊字符请使用URL编码)
     * @param string $filename 指定文件名(存在特殊字符请使用URL编码)
     * @return string 返回事件数组
     */
    static function forwardFile($type, $fromnum, $tonum, $fileid, $filename)
    {
        $requestFunctions = array('groupfiletogroup', 'groupfiletofriend', 'friendfiletofriend');
        $postData = array(($type > 1 ? 'logonqq' : 'fromqq') => self::$logonqq, ($type > 1 ? 'fromqq' : 'fromgroup') => $fromnum, ($type ? 'toqq' : 'togroup') => $tonum, 'fileId' => $fileid, 'fileName' => $filename);
        $result = self::sendRequest('forward' . $requestFunctions[$type], $postData);
        return self::parseResult($result);
    }

    /**
     * 查看转发聊天记录内容
     * @param string $resid 指定resid(xml消息中包含)
     * @return string 返回事件数组
     */
    static function getForwardedMsg($resid)
    {
        $postData = array('logonqq' => self::$logonqq, 'resid' => $resid);
        $result = self::sendRequest('getforwardedmsg', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }

    /**
     * 查询用户信息
     * @param number $toqq 指定欲查询QQ
     * @return string 返回事件数组
     */
    static function queryUserInfo($toqq)
    {
        $postData = array('logonqq' => self::$logonqq, 'qq' => $toqq);
        $result = self::sendRequest('queryuserinfo', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }

    /**
     * 查询群信息
     * @param number $togroup 指定欲查群号
     * @return string 返回事件数组
     */
    static function queryGroupInfo($togroup)
    {
        $postData = array('logonqq' => self::$logonqq, 'group' => $togroup);
        $result = self::sendRequest('querygroupinfo', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }

    /**
     * 发送免费礼物
     * @param number $togroup 指定群号
     * @param number $toqq 指定QQ
     * @param string $pkgid 指定礼物类型(299卡布奇诺;302猫咪手表;280牵你的手;281可爱猫咪;284神秘面具;285甜wink;286我超忙的;289快乐肥宅水;290幸运手链;313坚强;307绒绒手套; 312爱心口罩;308彩虹糖果)
     * @return string 返回事件数组
     */
    static function sendFreePackage($togroup, $toqq, $pkgid)
    {
        $postData = array('fromqq' => self::$logonqq, 'group' => $togroup, 'toqq' => $toqq, 'pkgid' => $pkgid);
        $result = self::sendRequest('sendfreepackage', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }

    /**
     * 发送免费礼物
     * @param number $toqq 指定欲查询QQ
     * @return string 返回事件数组
     */
    static function getQqOnlineState($toqq)
    {
        $postData = array('logonqq' => self::$logonqq, 'toqq' => $toqq);
        $result = self::sendRequest('getqqonlinestate', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }

    /**
     * 分享音乐
     * @param number $totype 指定分享对象类型(0私聊 1群聊  默认0)
     * @param number $to 指定分享对象(分享的群或分享的好友QQ)
     * @param string $musicname 指定歌曲名(存在特殊字符请使用URL编码)
     * @param string $singername 指定歌手名(存在特殊字符请使用URL编码)
     * @param string $jumpurl 指定跳转地址(点击音乐json后跳转的地址)(存在特殊字符请使用URL编码)
     * @param string $wrapperurl 指定封面地址(音乐的封面图片地址)(存在特殊字符请使用URL编码)
     * @param string $fileurl 指定文件地址(音乐源文件地址，如https://xxx.com/xxx.mp3)(存在特殊字符请使用URL编码)
     * @param string $apptype 指定应用类型(0QQ音乐 1虾米音乐 2酷我音乐 3酷狗音乐 4网抑云音乐  默认0)
     * @return string 返回事件数组
     */
    static function shareMusic($totype, $to, $musicname, $singername, $jumpurl, $wrapperurl, $fileurl, $apptype)
    {
        $postData = array('logonqq' => self::$logonqq, 'totype' => $totype, 'to' => $to, 'musicname' => $musicname, 'singername' => $singername, 'jumpurl' => $jumpurl, 'wrapperurl' => $wrapperurl, 'fileurl' => $fileurl, 'apptype' => $apptype);
        $result = self::sendRequest('sharemusic', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }


    /**
     * 获取skey
     * @return string 返回事件数组
     */
    static function getSkey()
    {
        $postData = array('logonqq' => self::$logonqq);
        $result = self::sendRequest('getskey', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }

    /**
     * 获取clientkey
     * @return string 返回事件数组
     */
    static function getClientKey()
    {
        $postData = array('logonqq' => self::$logonqq);
        $result = self::sendRequest('getclientkey', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }

    /**
     * 获取skey
     * @param string $domain 指定域(tenpay.com;openmobile.qq.com;docs.qq.com;connect.qq.com;qzone.qq.com;vip.qq.com;gamecenter.qq.com;qun.qq.com;game.qq.com;qqweb.qq.com;ti.qq.com;office.qq.com;mail.qq.com;mma.qq.com)
     * @return string 返回事件数组
     */
    static function getPskey($domain)
    {
        $postData = array('logonqq' => self::$logonqq, 'domain' => $domain);
        $result = self::sendRequest('getpskey', $postData);
        $result = json_decode($result, true);
        return $result['ret'];
    }


    static function parseResult($result)
    {
        $data = json_decode($result, 1);
        $return = json_decode($data['ret'], 1);
        if (isset($return['random'])) $return['random'] = $data['random'];
        if (isset($return['req'])) $return['req'] = $data['req'];
        return $return;
    }


    static function sendRequest($func, $data)
    {
        $url = self:: $getway . '/' . $func;
        return self::curl_post($url, $data);
    }

    static function curl_post($url, $data)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        if (is_array($data)) {
            $data = http_build_query($data);
            $data = str_replace('+', '%20', $data);
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        if (self::$pass)
            curl_setopt($curl, CURLOPT_COOKIE, self::$pass);
        $tmpInfo = curl_exec($curl);
        curl_close($curl);
        return $tmpInfo;
    }

}
