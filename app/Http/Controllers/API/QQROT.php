<?php


namespace App\Http\Controllers\API;


use App\Models\ROT\Qcfg;
use App\Models\ROT\Qkey;
use GuzzleHttp;
use Illuminate\Support\Facades\DB;

/*私聊消息
{
	"Type": "PrivateMsg",
	"FromQQ": {
		"UIN": 3135491919,
		"NickName": "11"
	},
	"LogonQQ": 1963806765,
	"TimeStamp": {
		"Recv": 1610777812,
		"Send": 1610777812
	},
	"FromGroup": {
		"GIN": 0
	},
	"Msg": {
		"Req": 16092,
		"Seq": 72057595481063063,
		"Type": 166,
		"SubType": 134,
		"SubTempType": 0,
		"Text": "<?xml_version": "\'1.0\' encoding=\'UTF-8\' standalone=\'yes\' ?><msg serviceID="
		128 " templateID="
		12345 " action="
		native " brief=" [链接] 邀请你加入群聊 " sourceMsgId="
		0 " url="
		"><item layout="
		2 "><picture cover="
		"\/><title>邀请你加入群聊<\/title><summary \/><\/item><data groupcode="
		941046177 " groupname="
		12 对对对 " msgseq="
		1610777812259288 " msgtype="
		2 "\/><\/msg>",
		"BubbleID": 0
	},
	"Hb": {
		"Type": 0
	},
	"File": {
		"ID": "",
		"MD5": "",
		"Name": "",
		"Size": 0
	}
}
*/

/*群聊消息
{
    "Type": "GroupMsg",
	"FromQQ": {
    "UIN": 3135491919,
		"Card": "11",
		"SpecTitle": "",
		"Pos": {
        "Lo": 0,
			"La": 0
		}
	},
	"LogonQQ": 1963806765,
	"TimeStamp": {
    "Recv": 1610778422,
		"Send": 1610778423
	},
	"FromGroup": {
    "GIN": 511476741,
		"name": "测试"
	},
	"Msg": {
    "Req": 62873,
		"Random": 3292921933,
		"SubType": 134,
		"AppID": 0,
		"Text": "3333333333333",
		"Text_Reply": "",
		"BubbleID": 4
	},
	"File": {
    "ID": "",
		"MD5": "",
		"Name": "",
		"Size": 17179869184
	}
}*/


class QQROT
{
    public $data;
    public $qun;
    public $qunKey;//授权群 关键词


    public $type;//信息类型  群、好友
    public $qq;
    public $key;//请求关键词

    public $startKey;//请求关键词
    public $endKey; //请求关键词


    public $json = "";//当前消息转换为json格式
    public $str = "";//当前消息转换为str格式
    public $img = "";//当前消息转换为图片格式


    public $defind_img = 'https://pic.downk.cc/item/5f526091160a154a67925a49.png';
    public $defind_aideo = 'http://url.ehua.pro/3S696';

    public function __construct()
    {
        /*
           {
               "Type": "GroupMsg",
               "FromQQ": {
                   "UIN": 3135491919,
                   "Card": "_Ehua",
                   "SpecTitle": "",
                   "Pos": {
                       "Lo": 0,
                       "La": 0
                   }
               },
               "LogonQQ": 1963806765,
               "TimeStamp": {
                   "Recv": 1600621234,
                   "Send": 1600621234
               },
               "FromGroup": {
                   "GIN": 511476741,
                   "name": "测试"
               },
               "Msg": {
                   "Req": 62414,
                   "Random": 3296550667,
                   "SubType": 134,
                   "AppID": 0,
                   "Text": "搜极限挑战",
                   "Text_Reply": "",
                   "BubbleID": 4
               },
               "File": {
                   "ID": "",
                   "MD5": "",
                   "Name": "",
                   "Size": 17179869184
               }
           }
        */
        if (request()->has('bool')) {//调试模式
            $json = file_get_contents('1.json');
            $this->data = json_decode($json, true);
        } else {

            $json = array_keys(request()->all())[0];
            $json = stripslashes($json);
            //强制换格式
            $json = str_replace("<?xml_version\":\"\'1.0\' encoding=\'UTF-8\' standalone=\'yes\' ?>", "", $json);

            $this->data = json_decode($json, true);
            if (preg_match("/.*xml_version.*/", $json)) {
                $this->data = [];
            }//xml强制为空
            if (empty($this->data)) {
                $json = json_encode(request()->all(), 256);
//                DB::table('log')->insert(['datetime' => date('Y-m-d H:i:s', time()), 'body' => $json]);

                $json = stripslashes($json);
                //强制换格式
                $json = str_replace("\"_file,fileId\":\"", "\"_file,fileId\\\":\\\"", $json);
                $json = str_replace("<?xml_version\":\"\'1.0\' encoding=\'UTF-8\' standalone=\'yes\' ?>", "", $json);


                $json = trim($json, '{');
                $json = trim($json, '}');
                $json = trim($json, '"');
                file_put_contents('11.txt', $json);
                DB::table('log')->insert(['body' => $json]);

                $this->data = json_decode($json, true);
//                DB::table('log')->insert(['body' => 'run out']);
            }
            if (empty($this->data)) {
                DB::table('log')->insert(['body' => 'data is null']);
                return;
            }
        }

        //初始化
        $this->qq = $this->data['FromQQ']['UIN'];
        $this->qun = $this->data['FromGroup']['GIN'];
        $this->key = $this->data['Msg']['Text'];
        $this->type = $this->data['Type'];
        $qunKey = DB::table('q_auth_qun')->where('qun', $this->qun)->value('key');


        if ($this->qq == config('QQROT.qq')) {
            return;
        }//调过自己的消息
        \QQROT\QQROT::init(config('QQROT.qq'), config('QQROT.ip'), config('QQROT.port'), config('QQROT.pass'));

        if ($this->key == '测试') {
            \QQROT\QQROT::sendGroupMsg($this->qun, 'Success!', $anonymous = false, 'str');//给群：12345 发消息
        }


        //监控
        switch ($this->type) {
            case 'PrivateMsg'://好友信息
                QQROT_CHECK::groupshare($this->key, $this->qq);//群邀请自动同意监控
                break;
            case 'GroupMsg':
                QQROT_CHECK::monitoringMsg($this->key, $this->qun, $this->qq);//群信息采集监控
                QQROT_CHECK::groupMsgshare($this->key, $this->qun, 'die');//群消息转发监控（群文件）
                break;
        }
        if (empty($qunKey)) { die; }//群未授权 die
        $this->qunKey = explode(',', $qunKey);

        //菜单命令监控（优先）
        QQROT_CHECK::groupMenu($this->key, $this->qun, $this->qunKey, 'die');
    }

    public function Run()
    {
//   <?xml version='1.0' encoding='UTF-8' standalone='yes' <!--<msg serviceID="128" templateID="12345" action="native" brief="[链接]邀请你加入群聊" sourceMsgId="0" url=""><item //layout="2"><picture cover=""/><title>邀请你加入群聊</title><summary /></item><data groupcode="953540518" groupname="小狼狗" msgseq="1610834617813036" msgtype="2"/></msg>


        switch ($this->type) {
            case 'PrivateMsg'://好友信息


                break;
            case 'GroupMsg'://群消息

                //一 匹配库内关键词
                $res = $this->is_keyall($this->key, $ids = $this->qunKey);//判断是否为全索引指令
                if (empty($res)) {
                    $res = $this->getkey($this->key, 5, $ids = $this->qunKey);
                }

                if (empty($res)) {
                    return $this->put_error('No keyword is matched');
                }//无匹配则返回
                $res = $res->toarray();

                // 查询关键词对应操作
                $CONFIG = Qcfg::where(['id' => $res['config_id']])->first();
                if (empty($CONFIG)) {
                    return $this->put_error('No configuration');
                }
                $CONFIG = $CONFIG->toarray();


                //二 逻辑处理 判断是否含有特殊事件标签
                $this->authority_labe($res);

                //三 逻辑处理 输出格式处理 针对配置替换内容
                $this->authority_type($CONFIG);

                //四 输出执行操作
                return $this->put_success($this->qq, $CONFIG['type']);


                break;
        }
    }


    /**
     * 判断是否包含官方标签 进行特殊逻辑输出
     * @param $res
     * @author Ehua(ehua999@163.com)
     * @date 2021/1/16 12:22
     */
    public function authority_labe($res)
    {
        switch ($res['labe']) {
            case 'search'://GET搜索
                $str = $res['content'] . urlencode($this->endKey);
                $this->json = "点击查看|{$str}|{$this->defind_aideo}|$this->defind_img|EHUA ROT|搜索结果：{$this->endKey}|搜索结果";
                $this->str = $str;
                $this->img = $str;
                break;
            case 'search3'://GET搜索
                $content = explode('|', $res['content']);
                foreach ($content as $kk => $k) {
                    $arr = $this->guzz('http://url.ehua.pro/api.php?url=' . $k . urlencode($this->endKey));
                    $arr = json_decode($arr, true);
                    if (!empty($arr["error"])) {
                        $str[] = '结果' . ($kk + 1) . ':' . $arr['error'];
                    } else {
                        $str[] = '结果' . ($kk + 1) . ':' . $arr['shorturl'];
                    }
                }
                $str = '查询结果：

' . implode("

", $str);
                $this->json = "点击查看|{$str}|{$this->defind_aideo}|$this->defind_img|EHUA ROT|搜索结果：{$this->endKey}|搜索结果";
                $this->str = $str;
                $this->img = $str;
                break;
            case 'shorturl'://生成短链
                $arr = $this->checkUrl('http://url.ehua.pro/api.php?url=' . urlencode($this->endKey));
                if (!empty($arr["error"])) {
                    $str = $arr['error'];
                } else {
                    $str = $arr['shorturl'];
                }

                $this->json = "$str|{$str}|{$this->defind_aideo}|$this->defind_img|EHUA ROT|生成完毕|搜索结果";
                $this->str = $str;
                $this->img = $str;
                break;
            case 'oneword'://一言
                $temp = $this->checkUrl2('https://api.uomg.com/api/rand.qinghua?format=json');
                $str = $temp['content'];
                $this->json = "$str|$this->defind_aideo|$this->defind_aideo|$this->defind_img|EHUA ROT| |一言";
                $this->str = $str;
                $this->img = '';
                break;
            case 'headimg'://随机头像
                $temp = $this->checkUrl2('https://api.uomg.com/api/rand.avatar?format=json');
                $str = $temp['imgurl'];

                $this->json = "$str|{$str}|{$str}|{$str}|EHUA ROT|图片分享|图片分享";
                $this->str = $str;
                $this->img = $str;
                break;
            case 'showimg'://随机头像
                $temp = $this->checkUrl2('https://api.uomg.com/api/rand.img2?sort=%E8%85%BF%E6%8E%A7&format=json');
                $str = $temp['imgurl'];

                $this->json = "$str|{$str}|{$str}|{$str}|EHUA ROT|图片分享|图片分享";
                $this->str = $str;
                $this->img = $str;
                break;
            case 'audio':
                $temp = $this->checkUrl2('https://api.uomg.com/api/rand.music?sort=%E6%8A%96%E9%9F%B3%E6%A6%9C&format=json');
                $temp = $temp['data'];
                /*
                 * array(2) {
                      ["code"]=>
                      int(1)
                      ["data"]=>
                      array(4) {
                        ["name"]=>
                        string(6) "想说"
                        ["url"]=>
                        string(55) "http://music.163.com/song/media/outer/url?id=1461142410"
                        ["picurl"]=>
                        string(71) "http://p4.music.126.net/_P62J8rc2wRIzoqIJGu2LA==/109951165120360797.jpg"
                        ["artistsname"]=>
                        string(9) "颜人中"
                      }
                    }*/
                $this->json = "{$temp['artistsname']}|{$temp['url']}|{$temp['url']}|{$temp['picurl']}|EHUA ROT|{$temp['name']}|{$temp['name']}";
                $this->str = "{$temp['artistsname']}|{$temp['url']}|{$temp['url']}|{$temp['picurl']}|EHUA ROT|{$temp['name']}|{$temp['name']}";
                $this->img = '';
                break;
            case 'csgokey'://csgo在线开黑
                $time = time() - 60 * 5;
                $res = DB::table('made_csgo')
                    ->where('intime', '>', date('Y-m-d H:i:s', $time))
                    ->limit(10)
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

                $this->json = "$str|{$str}|{$str}|{$str}|EHUA ROT|信息分享|信息分享";
                $this->str = $str;
                $this->img = $str;
                break;
            default:
                $this->json = $res['content'];
                $this->str = $res['content'];
                $this->img = $res['content'];
                break;
        }
    }

    /**
     * 输出格式处理 针对配置替换内容
     * @param $CONFIG
     * @author Ehua(ehua999@163.com)
     * @date 2021/1/16 12:26
     */
    public function authority_type($CONFIG)
    {
        if ($CONFIG['type'] == 'img') {

            $arr = explode('|', $this->img);
            $num = count($arr);
            $this->img = $CONFIG['content'];
            for ($i = $num - 1; $i >= 0; $i--) {
                $ii = $i + 1;
                $this->img = str_replace("【内容{$ii}】", '【图片' . $arr[$i] . '】', $this->img);
            }

        } elseif ($CONFIG['type'] == 'str') {
            $arr = explode('|', $this->str);
            $num = count($arr);

            $this->str = $CONFIG['content'];
            for ($i = $num - 1; $i >= 0; $i--) {
                $ii = $i + 1;
                $this->str = str_replace("【内容{$ii}】", $arr[$i], $this->str);
            }
        } else {
            $arr = explode('|', $this->json);
            $num = count($arr);

            $this->json = $CONFIG['content'];
            for ($i = $num - 1; $i >= 0; $i--) {
                $ii = $i + 1;
                $this->json = str_replace("【内容{$ii}】", $arr[$i], $this->json);
            }
        }
    }

    /**
     * 匹配是否为like关键词
     * @param $key
     * @param int $loac
     * @return bool
     */
    private function getkey($key, $loac = 3, $ids)
    {
        if ($loac >= 1) {
            $splitKey = $this->split($key, $loac);//分割关键词
            $res = $this->is_keylike($splitKey['start'], $ids);//判断是否为like关键词
            if (empty($res)) {
                return $this->getkey($key, $loac - 1, $ids);
            } else {
                return $res;
            }
        } else {
            return false;
        }
    }

    /**
     * 匹配是否为全索引关键词
     * @param $key
     * @return mixed
     */
    private function is_keyall($key, $ids)
    {
        $res = Qkey::where('key', $key);
        $res = $res->whereIn('id',$ids);
        $res = $res->first();
        return $res;
    }

    private function is_keylike($key, $ids)
    {
        $res = Qkey::where('key', 'like', $key);
        $res = $res->whereIn('id', $ids);
        $res = $res->first();
        return $res;
    }

    /**
     * 分割关键词
     * @param $key
     * @param $loca
     * @return array
     */
    private function split($key, $loca)
    {
        $start = mb_substr($this->key, 0, $loca, 'utf-8'); //截取  第 $loca 个汉字
        $end = mb_substr($this->key, $loca);

        $this->startKey = $start;
        $this->endKey = $end;

        return ['start' => $start, 'end' => $end];
    }

    //没有关键词匹配到 返回
    private function put_error($str = 'ERROR')
    {
//        response()->json(['code' => 200, 'msg' => '响应成功', 'type' => 'str', 'data' => $str]);
//        $content = $str;
//        if ($this->qun == 0) {
//            \QQROT\QQROT::sendPrivateMsg($this->qq, $content, 'str');//给QQ：12345 发好友消息
//        } else {
//            \QQROT\QQROT::sendGroupMsg($this->qun, $content, $anonymous = true, 'str');//给群：12345 发消息
//        }
    }

    /**
     * 输出消息
     * @param $qq
     * @param $content
     * @param $type     消息类型 xml json str
     */
    private function put_success($qq, $type = '')
    {

        switch ($type) {
            case 'str'://need str
                $content = $this->str;
                if ($this->qun == 0) {
                    \QQROT\QQROT::sendPrivateMsg($qq, $content, $type);//给QQ：12345 发好友消息
                } else {
                    \QQROT\QQROT::sendGroupMsg($this->qun, $content, $anonymous = true, $type);//给群：12345 发消息
                }
                break;
            case 'json'://need json
//                var_dump($content);die;
                $content = $this->json;

                if ($this->qun == 0) {
                    $res = \QQROT\QQROT::sendPrivateMsg($qq, $content, $type);//给QQ：12345 发好友消息
                } else {
                    $res = \QQROT\QQROT::sendGroupMsg($this->qun, $content, $anonymous = true, $type);//给群：12345 发消息
                }
                break;
            case 'img'://need url
                $content = $this->img;

                return response()->json(['code' => 200, 'msg' => '响应成功', 'type' => $type, 'data' => $content]);
                break;
            case 'event':
                break;
//            case 'share'://need url
//                if ($this->qun == 0) {
//                    \QQROT\QQROT::shareMusic(0, $this->qq, $this->shareTitle, $this->shareDescribe, $content, $this->sharePicurl, $this->shareFile, 5);
//                } else {
//                    \QQROT\QQROT::shareMusic(1, $this->qun, $this->shareTitle, $this->shareDescribe, $content, $this->sharePicurl, $this->shareFile, 5);
//                }
//                break;
            default:
                return $this->put_error('No matching return type');
                break;
        }


    }


    //请求接口操作
    private function checkUrl($url)
    {
        $client = new GuzzleHttp\Client();
        $res = $client->request('GET', $url, [
        ]);

        $json = (string)$res->getBody();
//        $json = substr($json, 3);
        $arr = json_decode($json, true);
        return $arr;
    }

    //请求接口操作
    private function checkUrl2($url)
    {
        preg_match("/[\w]+\.[\w][\w-]*\.(?:com\.cn|com|cn|co|net|org|gov|cc|biz|info)(|$)/isU", $url, $domain);
        $client = new GuzzleHttp\Client();
        $res = $client->request('GET', $url, [
            'headers' => ['host' => $domain[0]],
            'verify' => false,
        ]);
        $json = (string)$res->getBody();
        $arr = json_decode($json, true);

        return $arr;
    }


    function guzz($url = null, $field = null, $mothod = 'get', $header = null, $proxy = null, $outtime = 15)
    {
        if (is_array($field)) {
            $type = 'form_params';
        } else {
            $field = json_decode($field);
            $type = 'json';
        }

        $client = new GuzzleHttp\Client(); //初始化客户端

        if ($mothod == 'get' || $mothod == 'GET') {
            $response = $client->get($url, [
                'query' => $field,
                'header' => $header,
                'timeout' => $outtime, //设置请求超时时间
                'verify' => false,
                'proxy' => $proxy,
            ]);
        } else {
            $response = $client->request('POST', $url, [
                $type => $field,
                'header' => $header,
                'timeout' => $outtime,
                'verify' => false,
                'proxy' => $proxy,
            ]);
        }

        $body = $response->getBody(); //获取响应体，对象
        $bodyStr = (string)$body; //对象转字串,这就是请求返回的结果

        $bodyStr = trim($bodyStr, "\xEF\xBB\xBF");
        return $bodyStr;
    }
}
