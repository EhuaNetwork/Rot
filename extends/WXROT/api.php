<?php

/** 
* @name 接收消息demo（php版）
* @desc 类似于chat公众号的接口地址，所有消息都会推送到这里，整个流程只需要阅读本demo即可，不依赖其他文件
* @link www.keaimao.com
* @author winn bug反馈 新功能定制请联系QQ：1525555556
* @version 3.0
* @copyright by lovelycat team
*/



/**
 * @todo  以下为http请求中附带过来的参数，以取出type为例，php可用$_POST['type']，java中可用request.getparameter("type")取出
 * @param int type                 =>   事件类型（事件列表可参考 - 事件列表demo）
 * @param int msg_type             =>   消息类型（仅在私聊和群消息事件中，代表消息的表现形式，如文字消息、语音、等等）
 * @param string from_wxid         =>   1级来源id（比如发消息的人的id）
 * @param string from_name         =>   1级来源昵称（比如发消息的人昵称）
 * @param string final_from_wxid   =>   2级来源id（群消息事件下，1级来源为群id，2级来源为发消息的成员id，私聊事件下都一样）
 * @param string final_nickname    =>   2级来源昵称
 * @param string robot_wxid        =>   当前登录的账号（机器人）标识id
 * @param string file_url          =>   如果是文件消息（图片、语音、视频、动态表情），这里则是可直接访问的网络地址，非文件消息时为空
 * @param string msg               =>   消息内容
 * @param string parameters        =>   附加参数（暂未用到，请忽略）
 * @param int time                 =>   请求时间(时间戳10位版本)
 */

include 'interface.php';

date_default_timezone_set('PRC');

$type = $_POST['type'];

$msg_type = $_POST['msg_type'];

$from_wxid = $_POST['from_wxid'];	  

$nickname  = urldecode($_POST['from_name']);  // 昵称会出现中文，需要转码

$final_from_wxid = $_POST['final_from_wxid'];

$final_from_name = urldecode($_POST['final_from_name']);

$robwxid   = $_POST['robot_wxid'];	  

$file_url  = urldecode($_POST['file_url']);

$msg  = urldecode($_POST['msg']);

$time = $_POST['time'];


// 这里可以拓展自己的逻辑代码，比如获取天气、物流、新闻、开奖信息等等

// 这里开始进入业务流程，模拟一个签到的功能

// 如果是私聊模式
if ($type==100) {
	
	// 判断用户发来的关键词
	if($msg=='签到'){

		// 自己的逻辑处理
		$money = '1022';
		$reply = '恭喜你，领取签到成功，当前金币剩余：' . $money;

		// 封装返回数据结构
		$data = array();
		$data['type'] = 100;                 // Api数值（可以参考 - api列表demo）
		$data['msg']  = urlencode($reply);   // 发送内容
		$data['to_wxid'] = $from_wxid;       // 对方id（默认发送至来源的id，也可以发给其他人）
		$data['robot_wxid'] = $robwxid;      // 发消息的账号id，默认是收到消息的这个号，如登录了两个号，也可以用其他的号发出
		$data['key'] = 'xxxxxxxxxxxxx';      // 忽略，在v2.3及以上启用了key验证才需要配这个
		$response = array('data' => json_encode($data));

		// 然后http请求
		$url = 'http://127.0.0.1:8073/send';
		sendSGHttp($url, $response,'post');

		// 流程非常简单，构造一个指定的json，然后http请求接口即可
		// http post 参数为 data={"type":100, "msg":"恭喜你，签到已完成，获得金币5个", "to_wxid":"wxid_1xxxx", "robot_wxid":"wxid_fxxxxx"}

		// 也可以直接使用封装好的API接口方法（php版），解除注释下面这一句即可，其他语言请参考上面的过程编写
		// echo send_text_msg($robwxid, $from_wxid, $reply);


		// 整个流程结束
		exit;

	}


}


// 返回格式，无要求

?>