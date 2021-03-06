<?php


namespace App\Http\Controllers\API;


use App\Models\ROT\Qcfg;
use App\Models\ROT\Qkey;
use App\Models\ROT\Qkey_Boss;
use GuzzleHttp;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\DeclareDeclare;


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
//            $json = str_replace("0\\x11\\x10", '', $json);
            $this->data = json_decode($json, true);
        } else {
            $json = request()->all();
            $json = x16($json);
            $json = json_encode($json, 256);

            DB::table('log')->insert(['datetime' => date('Y-m-d H:i:s', time()), 'body' => $json]);
            $this->data = json_decode($json, true);
            if (empty($this->data)) {
                DB::table('log')->insert(['body' => 'data is null']);
                return;
            }
        }
        //初始化
        $this->qq = empty($this->data['sender']['id']) ? $this->data['trigger']['id'] : $this->data['sender']['id'];
        if (DB::table('q_system')->where(['value' => 1, 'key' => 'Status'])->count() != 0) {
            $bossqq = DB::table('q_system')->where(['key' => 'Boos'])->first();
            $bossqq = json_encode($bossqq, 256);
            $bossqq = json_decode($bossqq, true);
            $new_arr = explode(',', $bossqq['value']);
            if (!in_array($this->qq, $new_arr)) {
                return false;
            } else {
                $bool1 = 1;
            }
        }
        file_put_contents('1.json', $json);

        $this->qun = @$this->data['group']['id'];
        $this->key = $this->data['message']['text'];
        $this->type = $this->data['type'];
        $this->subtype = $this->data['subtype'];


        $quninfo = DB::table('q_auth_qun')->where('qun', $this->qun)->first(['key', 'add_qun']);
        $quninfo = json_encode($quninfo, 256);
        $quninfo = json_decode($quninfo, true);

        \QQROT\QQROT2::init(config('QQROT.qq'), config('QQROT.ip'), (config('QQROT.port') + 1), config('QQROT.pass'));
        if (!empty($bool1)) {
            $RES = \QQROT\QQROT2::send_friend_msg('3135491919', '当前为调试模式');
        }

        if ($this->qq == config('QQROT.qq')) {
            return;
        }//跳过自己消息
        if ($this->key == '测试') {
            \QQROT\QQROT2::send_group_msg($this->qun, 'Success!', $anonymous = false);
        }
        if ($this->qun == '1072639978') {
            return false;
            die;
        }
        if ($this->qq == '2854196310') {
            return false;
            die;
        }


        //监控
        switch ($this->subtype) {
            case 'MemberApplyJoin'://事件消息

                QQROT_CHECK2::groupadd($this->key, $this->qun, $this->qq, $quninfo['add_qun'], $this->data);//加群处理

                break;
            case 'private'://好友信息

                QQROT_CHECK2::groupshare($this->key, $this->qq, $this->data['message']['id']);//群邀请自动同意监控
//                QQROT_CHECK2::groupshareHe($this->key, $this->qq,$this->data['message']['id']);//群邀请自动同意监控
                QQROT_CHECK2::groupadd2($this->key, $this->qq, 'die');//加群处理2
                break;
            case 'group':
                //主人授权命令
                $this->boosCommand();
                T_yunhei::yunhei($this->key, $this->qun, $this->qq);//云黑
                T_csgo::csgopub($this->key, $this->qun);//csgo小功能
                T_Qbang::Qbang($this->key, $this->qun);//查Q绑
                T_mm::mm($this->key, $this->qun);//营养快线
                T_admin::init($this->key, $this->qun, $this->qq);

//                T_MsgRot::MsgRot($this->key,$this->qun);//聊天机器人

                QQROT_CHECK2::monitoringMsg($this->key, $this->qun, $this->qq);//群信息采集监控
                QQROT_CHECK2::groupMsgshare($this->key, $this->qun, $this->qq, 'die');//群消息转发监控（群文件）
                break;
        }


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
     * 主人命令
     * @return \Illuminate\Http\JsonResponse|void
     * @author Ehua(ehua999@163.com)
     * @date 2021/1/28 17:17
     */
    public function boosCommand()
    {
        switch ($this->subtype) {
            case 'private'://好友信息


                break;
            case 'group'://群消息

                //一 匹配库内关键词
                $res = $this->is_keyall_boss($this->key, $ids = $this->qunKey);//判断是否为全索引指令
                if (empty($res)) {
                    $res = $this->getkey_boss($this->key, 5, $ids = $this->qunKey);
                }
                if (empty($res)) {
                    return false;//todo 无主人指令
                }//无匹配则返回
                $res = $res->toarray();


                // 查询关键词对应操作
                $CONFIG = Qcfg::where(['id' => $res['config_id']])->first();
                if (empty($CONFIG)) {
                    return $this->put_error('No configuration');
                }
                $CONFIG = $CONFIG->toarray();


                //二 逻辑处理 判断是否含有特殊事件标签
                $this->authority_labe_boss($res);

                //三 逻辑处理 输出格式处理 针对配置替换内容
                $this->authority_type($CONFIG);

                //四 输出执行操作
                return $this->put_success($this->qq, $CONFIG['type']);


                break;
        }
    }
//=====================================================================================================================

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
                    $arr = guzz('http://url.ehua.pro/api.php?url=' . $k . urlencode($this->endKey));
                    $arr = json_decode($arr, true);
                    if (!empty($arr["error"])) {
                        $str[] = '结果' . ($kk + 1) . ':' . $arr['error'];
                    } else {
                        $str[] = '结果' . ($kk + 1) . ':' . $arr['shorturl'];
                    }

//                    $str[] = '结果' . ($kk + 1) . ':' . $k . urlencode($this->endKey);
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
                $str = T_csgo::csgokey();

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

//============================================================================================================

    /**
     * 匹配是否为like关键词
     * @param $key
     * @param int $loac
     * @return bool
     */
    private function getkey_boss($key, $loac = 3, $ids)
    {
        if ($loac >= 1) {
            $splitKey = $this->split($key, $loac);//分割关键词
            $res = $this->is_keylike_boss($splitKey['start'], $ids);//判断是否为like关键词
            if (empty($res)) {
                return $this->getkey_boss($key, $loac - 1, $ids);
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
    private function is_keyall_boss($key, $ids)
    {

        $res = Qkey_Boss::where('key', $key);
        $res = $res->first();

        return $res;
    }

    private function is_keylike_boss($key, $ids)
    {
        $res = Qkey_Boss::where('key', $key)->where('key', 'like', $key);
        $res = $res->first();
        return $res;
    }

    /**
     * 判断是否包含官方标签 进行特殊逻辑输出
     * @param $res
     * @author Ehua(ehua999@163.com)
     * @date 2021/1/16 12:22
     */
    public function authority_labe_boss($res)
    {
        switch ($res['labe']) {
            case 'touwenjian'://偷文件

                $arr = explode('-', $this->endKey);
                T_getfile::get_file($arr[0], $this->qun, $arr[1]);
                $str = '执行完毕';
                $this->json = "$str|{$str}|{$str}|{$str}|EHUA ROT|信息分享|信息分享";
                $this->str = $str;
                $this->img = $str;
                break;
            case 'ququnliebiao'://取群列表

                $RES = \QQROT\QQROT2::get_group_list();
                $str = '';
                foreach ($RES as $y => $k) {
                    if ($k['id'] != '') {
                        $str .= ($y + 1) . '-' . $k['name'] . "\n";
                    }
                }
                $this->json = "$str|{$str}|{$str}|{$str}|EHUA ROT|信息分享|信息分享";
                $this->str = $str;
                $this->img = $str;
                break;

            case 'qunxinxi'://群信息


                $RES = \QQROT\QQROT2::get_group_list();
                $qunid = $RES[($this->endKey - 1)]['id'];
                $qunnum = $RES[($this->endKey - 1)]['num'];
                $owner = $RES[($this->endKey - 1)]['owner'];
                $RES = \QQROT\QQROT2::get_group_info($qunid);

                $str = "";
                $str .= "群名称：" . $RES['name'];
                $str .= "\n群号：" . $qunid;
                $str .= "\n群类型：" . $RES['type'];
                $str .= "\n群标签：" . @implode(',', $RES['tag']);
                $str .= "\n群简介：" . $RES['desc'];
                $str .= "\n群人数：" . $qunnum;
                $str .= "\n群主Q：" . $owner;

                $this->json = "$str|{$str}|{$str}|{$str}|EHUA ROT|信息分享|信息分享";
                $this->str = $str;
                $this->img = $str;
                break;
            case 'jiaqun'://加群

                $qun = explode('-', $this->endKey)[0];
                $msg = explode('-', $this->endKey)[1];
                $res = \QQROT\QQROT2::add_group($qun, $msg);
                $str = '已经申请';
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











//===================================================================================

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

    /**
     * 没有关键词匹配到 返回
     * @param string $str
     * @author Ehua(ehua999@163.com)
     * @date 2021/1/28 17:28
     */
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

    /**
     * 请求接口操作
     * @param $url
     * @return mixed
     * @throws GuzzleHttp\Exception\GuzzleException
     * @author Ehua(ehua999@163.com)
     * @date 2021/1/28 17:29
     */
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

}
