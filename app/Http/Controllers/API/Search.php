<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MSearch;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use GuzzleHttp;

class Search extends Controller
{
    public $endStr = "\rtips:[搜]][发][看][播动漫]";
    public $startStr = "";


    public function getlist()
    {




//        /* 使用示例 */
//        $robot = array(
//            'qq' => '1963806765', //机器人QQ号码
//            'ip' => '47.93.232.49', //接口IP
//            'port' => '10429', //接口端口
//            'pass' => '150638', //密码
//        );
//        \QQROT\QQROT::init($robot['qq'], $robot['ip'], $robot['port'], $robot['pass']); //初始化
//
//        $temp = '你刚才是不是发送了' . \request()->key;
//        \QQROT\QQROT::sendPrivateMsg(3135491919, $temp);//给QQ：12345 发好友消息


        if (\request()->qun==0) {
            return response()->json(['code' => 200, 'msg' => '响应成功', 'type' => 'json', 'data' =>$this->getjson()]);
        }


        return $this->demo();
//        die;
//
//        echo '<pre>';
//        $key = '海贼王';
//        $data = MSearch::getlist($type = null, $page = 1, $key, $time = null, $api_num = 0);
//        var_dump($data);
    }

    public function demo2()
    {
        switch (\request()->key) {
            case '测试文字':
                return response()->json(['code' => 200, 'msg' => '响应成功', 'type' => 'str', 'data' => "我是文字"]);
                break;
            case '测试图片':
                return response()->json(['code' => 200, 'msg' => '响应成功', 'type' => 'str', 'data' => "我是文字2【图片http://photo.chd.edu.cn/_upload/article/images/34/62/0a931db248ce8ece7b39ce45faea/b9333e53-fb26-4e52-88bb-b2d3ee835e75.jpg】"]);
                break;
            case '测试XML':
                return response()->json(['code' => 200, 'msg' => '响应成功', 'type' => 'xml', 'data' => $this->getxml()]);
                break;
            case '测试JSON':
                return response()->json(['code' => 200, 'msg' => '响应成功', 'type' => 'json', 'data' => $this->getjson()]);
                break;
        }
    }

    public function demo()
    {
        $start = mb_substr(\request()->key, 0, 1, 'utf-8'); //截取  第一个汉字
        $end = mb_substr(\request()->key, 1);

        $res = $this->demo2();

        if (!empty($res)) {
            return $res;
        }

        switch ($start) {
            case '搜';

                $str = $this->checkUrl(config('video.web')[0] . urlencode($end));
                $str = $this->addStart($str);
                $str = $this->addEnd($str);
                break;
            case '发';
                $str = $this->checkUrl(config('video.web')[1] . urlencode($end));
                $str = $this->addStart($str);
                $str = $this->addEnd($str);
                break;
            case '看';
                $str = $this->checkUrl(config('video.web')[3] . urlencode($end));
                $str = $this->addStart($str);
                $str = $this->addEnd($str);
                break;
            case '播';
                $start = mb_substr(\request()->key, 0, 3, 'utf-8'); //截取  第一个汉字
                $end = mb_substr(\request()->key, 3);

                if ($start == '播动漫') {
                    $str = $this->checkUrl(config('video.web')['动漫'] . urlencode($end));
                } else {
                    $str = "Error：" . $start;
                }
                $str = $this->addStart($str);
                $str = $this->addEnd($str);
                break;
            case '短':
                $start = mb_substr(\request()->key, 0, 3, 'utf-8'); //截取  第一个汉字
                $end = mb_substr(\request()->key, 3);
                if ($start == '短网址') {
                    $str = $this->checkUrl($end);
                } else {
                    $str = "Error：" . $start;
                }
                break;
            default :
                $str = "Success";
                break;
        }
        return response()->json(['code' => 200, 'msg' => '响应成功', 'type' => 'str', 'data' => $str]);
    }

    //添加后缀
    public
    function addEnd($str)
    {
        return $str . $this->endStr;
    }

    //添加前缀
    public
    function addStart($str)
    {
        return $this->startStr . $str;
    }


    public function getjson()
    {
//        $json = ' {"app":"com.tencent.qqpay.qqmp.groupmsg","desc":"","view":"groupPushView","ver":"1.0.0.3","prompt":"[站长工具]","appID":"","sourceName":"","actionData":"","actionData_A":"","sourceUrl":"","meta":{"groupPushData":{"time":"","cancel_url":"http:\/\/www.baidu.com","fromIcon":"","report_url":"","bannerLink":"","fromName":"name","summaryTxt":"","bannerImg":"http://m.qpic.cn/psc?/V10j04bo412xvw/TmEUgtj9EK6.7V8ajmQrEI4EYkTC3nQSTR1f9SqN6ItNzJ6yO72OL7xPepo9bqLrioumPJYgAwsxfE9s4LE.kiDI0*o.3sHhOauhfeLNdEU!/b&bo=gAc4BIAHOAQBKQ4!&rf=viewer_4","bannerTxt":"Success","item1Img":""}},"config":{"forward":0,"showSender":1},"text":"","sourceAd":""}';
//       $json='{"app":"com.tencent.qqpay.qqmp.groupmsg","desc":"","view":"groupPushView","ver":"1.0.0.3","prompt":"描述3","appID":"","sourceName":"","actionData":"","actionData_A":"","sourceUrl":"","meta":{"groupPushData":{"time":"","cancel_url":"http:\/\/www.baidu.com","fromIcon":"","report_url":"","bannerLink":"","fromName":"name","summaryTxt":"标题","bannerImg":"http://baidu.com","bannerTxt":"描述","item1Img":""}},"config":{"forward":0,"showSender":1},"text":"","sourceAd":""}';
//        $json = '{"app":"com.tencent.gamecenter.gameshare","desc":"游戏分享","view":"noDataView","ver":"0.0.0.1","prompt":"[应用]游戏分享","appID":"","sourceName":"","actionData":"","actionData_A":"","sourceUrl":"","meta":{"shareData":{"height":360,"scene":"SCENE_SHARE_VIDEO","buttons":[{"url":"https:\/\/cfm.qq.com\/act\/a20190620act\/index.htm","text":"json卡片测试"}],"jumpUrl":"https:\/\/game.gtimg.cn\/images\/cfm\/act\/a20190620act\/index.mp4","width":640,"type":"video","cover":"https:\/\/game.gtimg.cn\/images\/cfm\/act\/a20190620act\/ark_bg.jpg","appid":"1104466820cfm","url":"https:\/\/game.gtimg.cn\/images\/cfm\/act\/a20190620act\/index.mp4"}},"config":{"forward":1,"type":"normal"},"text":"","sourceAd":""}';
//        $json='{"app":"com.tencent.miniapp","desc":"","view":"notification","ver":"0.0.0.1","prompt":"[应用]","appID":"","sourceName":"","actionData":"","actionData_A":"","sourceUrl":"","meta":{"notification":{"appInfo":{"appName":"全国疫情数据统计","appType":4,"appid":1109659848,"iconUrl":"http:\/\/gchat.qpic.cn\/gchatpic_new\/719328335\/-2010394141-6383A777BEB79B70B31CE250142D740F\/0"},"data":[{"title":"确诊","value":"80932"},{"title":"今日确诊","value":"28"},{"title":"疑似","value":"72"},{"title":"今日疑似","value":"5"},{"title":"治愈","value":"60197"},{"title":"今日治愈","value":"1513"},{"title":"死亡","value":"3140"},{"title":"今**亡","value":"17"}],"title":"中国加油，武汉加油","button":[{"name":"病毒：SARS-CoV-2，其导致疾病命名 COVID-19","action":""},{"name":"传染源：新冠肺炎的患者。无症状感染者也可能成为传染源。","action":""}],"emphasis_keyword":""}},"text":"","sourceAd":""}';
//        $json = '{"app":"com.microsoft.xiaoiceark","desc":"QQ小冰","view":"coverpage","ver":"1.0.0.5","prompt":"[应用]擎天论坛","meta":{"data":{"Data":{"formCode":"1000"},"Description":"这里可以自定义【换行】换行请使用：换行【换行】换行请使用：换行【换行】换行请使用：换行【换行】换行请使用：换行【换行】换行请使用：换行","Page":"MindReader","Title":"擎天xml卡片工厂","Image":"assets\/TextGame\/card2.jpg"}}}';
//        $json = '{"app":"com.tencent.gamecenter.act","desc":"心安测试","view":"commonView","ver":"1.0.0.1","prompt":"[应用]火影忍者","appID":"1104307008","sourceName":"火影忍者-疾风传","actionData":"","actionData_A":"com.tencent.KiHan","sourceUrl":"http:\/\/idaen.top","meta":{"shareData":{"openId":"1496146BE42696C12F35AB96AF32AF26","scene":"69","time":"1550146708","extData":{},"cover":"http%3a%2f%2fdlied5.qq.com%2fkihan%2fark%2fdrama%2f10.png","textBoxData":{"pic":"http%3A%2F%2Fzheli.org%2Fdata%2Fi20190216173537.png","title":"内容1","desc":"内容2"},"appid":"1104307008","url":"http%3a%2f%2fdlied5.qq.com%2fkihan%2fark%2fdrama%2fdrama_small_10.mp4"}},"config":{"forward":1,"type":"normal"},"text":"","sourceAd":""}';
//        $json = '{"app":"com.tencent.miniapp","desc":"","view":"notification","ver":"0.0.0.1","prompt":"有人@你","appID":"","sourceName":"","actionData":"","actionData_A":"","sourceUrl":"","meta":{"notification":{"appInfo":{"img_s":"","img":"http:\/\/t.cn\/ROpXeJI","appName":"QQID:3135491919","ext":"","iconUrl":"http://q2.qlogo.cn/headimg_dl?dst_uin=3135491919&amp;spec=100","appType":4,"appid":1108249016},"button":[{"name":"1","action":""},{"name":"2","action":""},{"name":"3","action":""},{"name":"4","action":""},{"name":"5","action":""},{"name":"6","action":""}],"emphasis_keyword":""}},"text":"","sourceAd":""}';
//        $json = '{"app":"com.tencent.qqpay.qqmp.groupmsg","desc":"","view":"groupPushView","ver":"1.0.0.7","prompt":"[QQ红包]爱腾讯爱生活","appID":"","sourceName":"","actionData":"","actionData_A":"","sourceUrl":"","meta":{"groupPushData":{"time":"","cancel_url":"http:\/\/www.baidu.com","fromIcon":"","report_url":"","bannerLink":"","fromName":"name","summaryTxt":" 3","bannerImg":"1","bannerTxt":"2","item1Img":""}},"text":"","sourceAd":""}';
//        $json = '{"app":"com.microsoft.xiaoiceark","desc":"QQ小冰","view":"coverpage","ver":"1.0.0.5","prompt":"[应用]爱腾讯爱生活","meta":{"data":{"Data":{"formCode":"1000"},"Description":"内容","Image":"assets\/TextGame\/card2.jpg"}}}';
//        $json = '{"app":"com.microsoft.xiaoiceark","desc":"","view":"coverpage","ver":"1.0.1.16","prompt":"爱腾讯，爱生活！","appID":"","sourceName":"","actionData":"","actionData_A":"","sourceUrl":"","meta":{"data":{"Data":{"formCode":"1000"},"Image":"apps\/TextGame\/LoveThinkTank\/Image.assets\/love_think_tank.jpg","Description":"内容","Page":"loveThinkTank","Title":""}},"text":"","sourceAd":""}';
//        $json = '{"app":"com.tencent.miniapp","desc":"爱腾讯","view":"all","ver":"1.0.0.89","prompt":"爱生活","meta":{"all":{"buttons":[{"action":"","name":""}],"jumpUrl":"","preview":"内容","summary":"1","title":"2"}},"config":{"forward":true},"config":{"forward":0,"showSender":0}}';
//        $json = '{"app":"com.tencent.miniapp","desc":"","view":"all","ver":"1.0.0.89","prompt":"hello world","meta":{"all":{"buttons":[{"action":"","name":"1"}],"jumpUrl":"","preview":"1","summary":"2","title":"3"}},"config":{"forward":true},"config":{"forward":0,"showSender":0}}';
        $json='{"app":"com.tencent.structmsg","config":{"autosize":true,"ctime":1599208455,"forward":true,"token":"b65f7c635b71a3e8dca5f3eb4d8b97af","type":"normal"},"desc":"音乐","extra":{"app_type":1,"appid":100497308},"meta":{"music":{"action":"","android_pkg_name":"","app_type":1,"appid":100497308,"desc":"小星星Aurora","jumpUrl":"http://music.163.com/song/media/outer/url?id=1369649924","musicUrl":"http://music.163.com/song/media/outer/url?id=1369649924","preview":"http://p4.music.126.net/y8M2B3moftkO1fRPcTvbOA==/109951165050129476.jpg","sourceMsgId":"0","source_icon":"","source_url":"","tag":"QQ音乐","title":"山妖"}},"prompt":"[分享]山妖","ver":"0.0.0.1","view":"music"}';
//        $json = '{"app":"com.microsoft.xiaoiceark","desc":"QQ小冰","view":"coverpage","ver":"1.0.0.5","prompt":"[应用]擎天论坛","meta":{"data":{"Data":{"formCode":"1000"},"Description":"这里可以自定义【换行】换行请使用：换行【换行】换行请使用：换行【换行】换行请使用：换行【换行】换行请使用：换行【换行】换行请使用：换行","Page":"MindReader","Title":"擎天xml卡片工厂","Image":"assets\/TextGame\/card2.jpg"}},"config":{"forward":0,"showSender":1},"text":"qtxml.cn","sourceAd":"","config":{"forward":0,"showSender":0}}';
//        全屏 ,"config":{"forward":0,"showSender":1},"text":"qtxml.cn","sourceAd":"","config":{"forward":0,"showSender":0}
        $json='{"app":"com.tencent.mobileqq.reading","desc":"","view":"singleImg","ver":"1.0.0.70","prompt":"[QQ红包]恭喜发财","appID":"","sourceName":"","actionData":"","actionData_A":"","sourceUrl":"","meta":{"singleImg":{"mainImage":"https:\/\/icon.qiantucdn.com\/\/20200604\/46b6c6a44ecdd4798a55d4de781900862","mainUrl":"mqqapi%3A%2F%2Fforward%2Furl%3Fs%3D%E2%80%99%2Btimestamp%2B%E2%80%99%26url_prefix%3DaHR0cHM6Ly9qcS5xcS5jb20vP193dj0xMDI3Jms9U2lZWUlkVDU"}},"config":{"forward":0,"showSender":0},"text":"","sourceAd":"","extra":""}
';
        $json='{"app":"com.microsoft.xiaoiceark","desc":"QQ小冰","view":"coverpage","ver":"1.0.0.5","prompt":"[应用]擎天论坛","meta":{"data":{"Data":{"formCode":"1000"},"Description":"这里可以自定义【换行】换行请使用：换行【换行】换行请使用：换行【换行】换行请使用：换行【换行】换行请使用：换行【换行】换行请使用：换行","Page":"MindReader","Title":"擎天xml卡片工厂","Image":"assets\/TextGame\/card2.jpg"}},"config":{"forward":0,"showSender":1},"text":"qtxml.cn","sourceAd":"","config":{"forward":0,"showSender":0}}';
        return $json;
    }

    public function getxml()
    {
        return '<?xml version="1.0" encoding="utf-8"?><msg flag= "0" serviceID="35" brief="QQ群抽礼物" templateID="1" bg="#ffffff" action="plugin" adverSign="1"><item bg="#ffffff" layout="5"><picture cover="http://dwz.cn/723wvk"/></item><item bg="#ffffff" layout="5"><picture cover="http://dwz.cn/723BZq"/></item><item><hr></hr></item><item layout="3"><button action="web" url="http://t.cn/R9BEjZ2">点这里抽礼物</button></item></msg>';
    }

    public function checkUrl($url)
    {
        $client = new GuzzleHttp\Client();
        $res = $client->request('GET', 'http://url.ehua.pro/api.php?url=' . $url, [
        ]);
        $json = (string)$res->getBody();
        $json = substr($json, 3);
        $arr = json_decode($json, true);
        if (!empty($arr["error"])) {
            return $arr['error'];
        } else {
            return $arr['shorturl'];
        }
    }
}
