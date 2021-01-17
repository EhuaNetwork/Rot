<?php


namespace App\Http\Controllers\API;


use App\Models\ROT\Vcfg;
use App\Models\ROT\Vkey;
use GuzzleHttp;
use Illuminate\Support\Facades\DB;

class WXROT
{
//    public $qun;
//    public $qq;
    public $key;//请求关键词
    public $startKey;//请求关键词
    public $endKey; //请求关键词

    public $from_wxid;//来源人id
    public $robwxid;//机器人id

//
//    public $json = "";//当前消息转换为json格式
    public $str = "";//当前消息转换为str格式
    public $img = "";//当前消息转换为图片格式

    public $robot = array(
        'API_URL' => 'http://47.93.232.49:8073/send',
        'KEY' => '67560122A0A84f5491441147E7109D31',
    );


//    public $defind_img = 'https://pic.downk.cc/item/5f526091160a154a67925a49.png';
//    public $defind_aideo = 'http://url.ehua.pro/3S696';

    /**
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
     * @todo  以下为http请求中附带过来的参数，以取出type为例，php可用$_POST['type']，java中可用request.getparameter("type")取出
     */
    //        $robwxid = 'wxid_dx8senvu6jzm22';
//        $from_wxid = 'wxid_rpfxp4w3jt2v22';
    public function Run()
    {
        //初始化
        $type = request()->type;
        $msg_type = request()->msg_type;
        $from_wxid = request()->from_wxid;
        $nickname = urldecode(request()->from_name);  // 昵称会出现中文，需要转码
        $final_from_wxid = request()->final_from_wxid;
        $final_from_name = urldecode(request()->final_from_name);
        $robwxid = request()->robot_wxid;
        $file_url = urldecode(request()->file_url);
        $msg = urldecode(request()->msg);
        $time = request()->time;

        //初始化
        $this->key = $msg;
        $this->robwxid = ($robwxid) ?: 'wxid_dx8senvu6jzm22';
        $this->from_wxid = ($from_wxid) ?: 'wxid_rpfxp4w3jt2v22';
        $robot = $this->robot;
        \WXROT\WXROT::init($robot);


        //一 匹配库内关键词
        $res = $this->is_keyall($this->key);//判断是否为全索引指令
        if (empty($res)) {
            $res = $this->getkey($this->key, 5);
        }
        if (empty($res)) {//无匹配则返回
            return $this->put_error('No keyword is matched');
        }
        $res = $res->toarray();

        $CONFIG = Vcfg::where(['id' => $res['config_id']])->first();
        if (empty($CONFIG)) {
            return $this->put_error('No configuration');
        }

        $CONFIG = $CONFIG->toarray();
        //二 逻辑处理 判断是否含有特殊事件标签
        switch ($res['labe']) {
            case 'search'://GET搜索
//                $arr = $this->checkUrl('http://url.ehua.pro/api.php?url=' . $res['content'] . urlencode($this->endKey));
//                if (!empty($arr["error"])) {
//                    $str = $arr['error'];
//                } else {
//                    $str = $arr['shorturl'];
//                }
                $str = $res['content'] . urlencode($this->endKey);

                $this->str = $str;
                $this->img = $str;
                break;
//            case 'shorturl':
//                $arr = $this->checkUrl('http://url.ehua.pro/api.php?url=' . urlencode($this->endKey));
//                if (!empty($arr["error"])) {
//                    $str = $arr['error'];
//                } else {
//                    $str = $arr['shorturl'];
//                }
//
//                $this->json = "$str|{$str}|{$this->defind_aideo}|$this->defind_img|EHUA ROT|生成完毕|搜索结果";
//                $this->str = $str;
//                $this->img = $str;
//                break;
//            case 'oneword'://一言
//                $temp = $this->checkUrl2('https://api.uomg.com/api/rand.qinghua?format=json');
//                $str = $temp['content'];
//                $this->json = "$str|$this->defind_aideo|$this->defind_aideo|$this->defind_img|EHUA ROT| |一言";
//                $this->str = $str;
//                $this->img = '';
//                break;
//            case 'headimg'://随机头像
//                $temp = $this->checkUrl2('https://api.uomg.com/api/rand.avatar?format=json');
//                $str = $temp['imgurl'];
//
//                $this->json = "$str|{$str}|{$str}|{$str}|EHUA ROT|图片分享|图片分享";
//                $this->str = $str;
//                $this->img = $str;
//                break;
//            case 'showimg'://随机头像
//                $temp = $this->checkUrl2('https://api.uomg.com/api/rand.img2?sort=%E8%85%BF%E6%8E%A7&format=json');
//                $str = $temp['imgurl'];
//
//                $this->json = "$str|{$str}|{$str}|{$str}|EHUA ROT|图片分享|图片分享";
//                $this->str = $str;
//                $this->img = $str;
//                break;
//            case 'audio':
//                $temp = $this->checkUrl2('https://api.uomg.com/api/rand.music?sort=%E6%8A%96%E9%9F%B3%E6%A6%9C&format=json');
//                $temp = $temp['data'];
//                /*
//                 * array(2) {
//                      ["code"]=>
//                      int(1)
//                      ["data"]=>
//                      array(4) {
//                        ["name"]=>
//                        string(6) "想说"
//                        ["url"]=>
//                        string(55) "http://music.163.com/song/media/outer/url?id=1461142410"
//                        ["picurl"]=>
//                        string(71) "http://p4.music.126.net/_P62J8rc2wRIzoqIJGu2LA==/109951165120360797.jpg"
//                        ["artistsname"]=>
//                        string(9) "颜人中"
//                      }
//                    }*/
//                $this->json = "{$temp['artistsname']}|{$temp['url']}|{$temp['url']}|{$temp['picurl']}|EHUA ROT|{$temp['name']}|{$temp['name']}";
//                $this->str = "{$temp['artistsname']}|{$temp['url']}|{$temp['url']}|{$temp['picurl']}|EHUA ROT|{$temp['name']}|{$temp['name']}";
//                $this->img = '';
//                break;
            default:
//                $this->json = $res['content'];
                $this->str = $res['content'];
                $this->img = $res['content'];
                break;
        }

        //二 逻辑处理 针对配置替换内容
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
        //四 输出执行操作
        return $this->put_success($this->robwxid,$this->from_wxid, $CONFIG['type']);
    }

    /**匹配是否为like关键词
     * @param $key
     * @param int $loac
     * @return bool
     */
    public function getkey($key, $loac = 3)
    {
        if ($loac >= 1) {
            $splitKey = $this->split($key, $loac);//分割关键词
            $res = $this->is_keylike($splitKey['start']);//判断是否为like关键词
            if (empty($res)) {
                return $this->getkey($key, $loac - 1);
            } else {
                return $res;
            }
        } else {
            return false;
        }
    }

    /**匹配是否为全索引关键词
     * @param $key
     * @return mixed
     */
    public function is_keyall($key)
    {
        $res = Vkey::where('key', $key)->first();
        return $res;
    }

    public function is_keylike($key)
    {
        $res = Vkey::where('key', 'like', $key)->first();
        return $res;
    }

    /**分割关键词
     * @param $key
     * @param $loca
     * @return array
     */
    public function split($key, $loca)
    {
        $start = mb_substr($this->key, 0, $loca, 'utf-8'); //截取  第 $loca 个汉字
        $end = mb_substr($this->key, $loca);

        $this->startKey = $start;
        $this->endKey = $end;

        return ['start' => $start, 'end' => $end];
    }

    //没有关键词匹配到 返回
    public function put_error($str = 'ERROR')
    {
//        \WXROT\WXROT::send_text_msg($this->robwxid, $this->from_wxid, $str);
    }

    /**输出消息
     * @param $qq
     * @param $content
     * @param $type     消息类型 xml json str
     */
    public function put_success($robwxid, $from_wxid, $type = '')
    {

        switch ($type) {
            case 'str'://need str
                $content = $this->str;
                $res = \WXROT\WXROT::send_text_msg($robwxid, $from_wxid, $content);
                break;
//            case 'img'://need url
//                $content = $this->img;
//
//                return response()->json(['code' => 200, 'msg' => '响应成功', 'type' => $type, 'data' => $content]);
//                break;
//            case 'event':
//                break;
////            case 'share'://need url
////                if ($this->qun == 0) {
////                    \QQROT\QQROT::shareMusic(0, $this->qq, $this->shareTitle, $this->shareDescribe, $content, $this->sharePicurl, $this->shareFile, 5);
////                } else {
////                    \QQROT\QQROT::shareMusic(1, $this->qun, $this->shareTitle, $this->shareDescribe, $content, $this->sharePicurl, $this->shareFile, 5);
////                }
////                break;
            default:
                return $this->put_error('No matching return type');
                break;
        }


    }


    //请求接口操作
    public
    function checkUrl($url)
    {
        $client = new GuzzleHttp\Client();
        $res = $client->request('GET', $url, [
        ]);

        $json = (string)$res->getBody();
        $json = substr($json, 3);
        $arr = json_decode($json, true);
        return $arr;
    }

    //请求接口操作
    public
    function checkUrl2($url)
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
}
