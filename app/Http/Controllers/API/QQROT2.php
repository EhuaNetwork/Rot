<?php


namespace App\Http\Controllers\API;


use App\Models\ROT\Qcfg;
use App\Models\ROT\Qkey;
use GuzzleHttp;
use Illuminate\Support\Facades\DB;


class QQROT2
{
    public $data;
    public $qun;
    public $qunKey;//授权群 关键词

    public $subtype;//信息类型
    public $type;//信息类型  群、好友
    public $qq;
    public $key;//请求关键词

    public $startKey;//请求关键词
    public $endKey; //请求关键词


    public $json = "";//当前消息转换为json格式
    public $str = "";//当前消息转换为str格式
    public $img = "";//当前消息转换为图片格式

    public function __construct()
    {
        /*
        {
                    "bot": 1963806765,
            "time": 1611217800,
            "type": "event",
            "subtype": "MemberApplyJoin",
            "group": {
                    "id": 146971736,
                "name": "CSGO泄参群"
            },
            "trigger": {
                    "id": 2676918399,
                "name": "霉女☆Smile"
            },
            "operator": {
                    "id": 0,
                "name": "Smile Ⅲ"
            },
            "message": {
                    "id": 1611217800288425,
                "type": 0,
                "text": "问题：干啥\n答案：。。"
            }
        }*/

        if (request()->has('bool')) {//调试模式
            $json = file_get_contents('1.json');
            $this->data = json_decode($json, true);
        } else {
            $json = json_encode(request()->all(), 256);
            DB::table('log')->insert(['datetime' => date('Y-m-d H:i:s', time()), 'body' => $json]);
            $this->data = json_decode($json, true);
            if (empty($this->data)) {
                DB::table('log')->insert(['body' => 'data is null']);
                return;
            }
        }

        $bossqq = '3135491919';

        //初始化
        $this->qq = empty($this->data['sender']['id']) ? $this->data['trigger']['id'] : $this->data['sender']['id'];
//        if ($this->qq != $bossqq) {
//            return false;
//        } else {
//            $bool1 = 1;
//        }
        file_put_contents('1.json', $json);


        $this->qun = @$this->data['group']['id'];
        $this->key = $this->data['message']['text'];
        $this->type = $this->data['type'];
        $this->subtype = $this->data['subtype'];


        $quninfo = DB::table('q_auth_qun')->where('qun', $this->qun)->first(['key', 'add_qun']);
        $quninfo = json_encode($quninfo, 256);
        $quninfo = json_decode($quninfo, true);

        \QQROT\QQROT2::init(config('QQROT.qq'), config('QQROT.ip'), (config('QQROT.port') + 1), config('QQROT.pass'));
        if (isset($bool1)) {
            $RES = \QQROT\QQROT2::send_friend_msg($bossqq, '当前为调试模式');
        }

        if ($this->qq == config('QQROT.qq')) {
            return;
        }//跳过自己消息
        if ($this->key == '测试') {
            \QQROT\QQROT2::send_group_msg($this->qun, 'Success!', $anonymous = false);
        }


        //监控
        switch ($this->subtype) {
            case 'MemberApplyJoin'://事件消息

                QQROT_CHECK2::groupadd($this->key, $this->qun, $this->qq, $quninfo['add_qun'], $this->data);//加群处理

                break;
            case 'private'://好友信息
                QQROT_CHECK2::groupshare($this->key, $this->qq,$this->data['message']['id']);//群邀请自动同意监控
//                QQROT_CHECK2::groupshareHe($this->key, $this->qq,$this->data['message']['id']);//群邀请自动同意监控
                QQROT_CHECK2::groupadd2($this->key, $this->qq,'die');//加群处理2
                break;
            case 'group':

                QQROT_CHECK2::monitoringMsg($this->key, $this->qun, $this->qq);//群信息采集监控
//                DB::table('log')->insert(['body'=>1]);

                QQROT_CHECK2::groupMsgshare($this->key, $this->qun, $this->qq, 'die');//群消息转发监控（群文件）
//                DB::table('log')->insert(['body'=>2]);

                break;
        }

        //主人授权命令
        QQROT_CHECK2::boosCommand($this->key, $quninfo, $this->qun, $this->qq, $bossqq, 'die');

        if (empty($quninfo)) {
            die;
        }//群未授权 die
        $this->qunKey = explode(',', $quninfo['key']);

        //菜单命令监控（优先）
        QQROT_CHECK2::groupMenu($this->key, $this->qun, $this->qunKey, 'die');


    }

    public function Run()
    {
//   <?xml version='1.0' encoding='UTF-8' standalone='yes' <!--<msg serviceID="128" templateID="12345" action="native" brief="[链接]邀请你加入群聊" sourceMsgId="0" url=""><item //layout="2"><picture cover=""/><title>邀请你加入群聊</title><summary /></item><data groupcode="953540518" groupname="小狼狗" msgseq="1610834617813036" msgtype="2"/></msg>


        switch ($this->subtype) {
            case 'private'://好友信息


                break;
            case 'group'://群消息

                //一 匹配库内关键词
                $res = $this->is_keyall($this->key, $ids = $this->qunKey);//判断是否为全索引指令
                if (empty($res)) {
                    $res = $this->getkey($this->key, 5, $ids = $this->qunKey);
                }

                if (empty($res)) {
                    return $this->put_error('No keyword is matched');
                }//无匹配则返回
                $res = $res->toarray();


                DB::table('log')->insert(['body' => 111111]);
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
            case 'search3'://GET搜索
                $content = explode('|', $res['content']);
                foreach ($content as $kk => $k) {
//                    $arr = $this->guzz('http://url.ehua.pro/api.php?url=' . $k . urlencode($this->endKey));
//                    $arr = json_decode($arr, true);
//                    if (!empty($arr["error"])) {
//                        $str[] = '结果' . ($kk + 1) . ':' . $arr['err'];
//                    } else {
//                        $str[] = '结果' . ($kk + 1) . ':' . $arr['url'];
//                    }

                    $str[] = '结果' . ($kk + 1) . ':' . $k . urlencode($this->endKey);
                }
                $str = '查询结果：

' . implode("

", $str);

                $this->json = '';
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
                    ->limit(20)
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
        $res = $res->whereIn('id', $ids);
        $res = $res->first();
        return $res;
    }

    private function is_keylike($key, $ids)
    {
        $res = Qkey::where('key', $key)->where('key', 'like', $key);
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
                    \QQROT\QQROT2::send_friend_msg($qq, $content);//给QQ：12345 发好友消息
                } else {
                    \QQROT\QQROT2::send_group_msg($this->qun, $content, $anonymous = true);//给群：12345 发消息
                }
                break;
            case 'json'://need json
//                var_dump($content);die;
                $content = $this->json;

                if ($this->qun == 0) {
                    $res = \QQROT\QQROT2::send_friend_msg($qq, $content);//给QQ：12345 发好友消息
                } else {
                    $res = \QQROT\QQROT2::send_group_msg($this->qun, $content, $anonymous = true);//给群：12345 发消息
                }
                break;
            case 'img'://need url
                $content = $this->img;

                return response()->json(['code' => 200, 'msg' => '响应成功', 'type' => $type, 'data' => $content]);
                break;
            case 'event':
                break;
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
