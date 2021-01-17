<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class MSearch extends Model
{
    /*
    * $type    影视类型
    *pg        页码
    * wd       关键词
    * h        时间
    * */
    public static function getlist($type = null, $page = 1, $key = '', $time = null, $api_num = 0)
    {
        $client = new Client(['timeout' => 10, 'verify' => false]);
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        $url = config('video.video.cj')[$api_num];

        $data = ['ac' => self::CheckAc($api_num, 'list'), 'wd' => $key, 'pg' => $page, 't' => $type, 'h' => $time];

        $response = $client->request('GET', $url, [
            'query' => $data,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
                'Accept-Language' => 'zh-CN,zh;q=0.9,en;q=0.8,sm;q=0.7',
                'Accept-Encoding' => 'gzip'
            ],
            'decode_content' => true,// 解密gzip
        ]);

        $API_DATA = (string)$response->getBody();
        $API_DATA = decode($API_DATA);
        $API_DATA = json_decode($API_DATA, true);
        $API_DATA = self::Chaeck_List_data($api_num, $API_DATA);
        return $API_DATA;

    }

    /*
     * $ids      数据id，多个，分割
     * $page       页码
     * $type        类型
     * $time        时间
     * */
    public static function getinfo($ids = '', $page = null, $type = null, $time = null, $api_num = 0)
    {
        $client = new Client(['timeout' => 10, 'verify' => false]);

        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

        $url = config('video.video.cj')[$api_num];
        $data = ['ac' => self::CheckAc($api_num, 'info'), 'ids' => $ids, 'pg' => $page, 't' => $type, 'h' => $time];


        $response = $client->request('GET', $url, [
            'query' => $data,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
                'Accept-Language' => 'zh-CN,zh;q=0.9,en;q=0.8,sm;q=0.7',
                'Accept-Encoding' => 'gzip'
            ],
            'decode_content' => true,// 解密gzip
        ]);

        $API_DATA = (string)$response->getBody();
        $API_DATA = decode($API_DATA);
        $API_DATA = json_decode($API_DATA, true);


        $API_DATA = self::Chaeck_Info_data($api_num, $API_DATA);
        $API_DATA['decode'] = config('video.video.jx')[0];
        return $API_DATA;
    }

    //$ac     接口类型
    //$class  内容|详情
    public static function CheckAc($ac, $class)
    {
        if ($class == 'list') {
            switch ($ac) {
                case 0:
                    return 'list';
                    break;
                case 1:
                    return 'list';
                    break;
            }
        } elseif ($class == 'info') {
            switch ($ac) {
                case 0:
                    return 'detail';
                    break;
                case 1:
                    return 'videolist';
                    break;
            }
        } else {
            die;
        }
    }

    public static function Chaeck_List_data($api_num, $data)
    {

        $new_data = [];
        switch ($api_num) {
            case 0:
                foreach ($data['list'] as $da) {
                    $new_data[] = [
                        'id' => $da['vod_id'],
                        'title' => $da['vod_name'],
                        'uptime' => $da['vod_time'],
                        'type' => $da['type_id'],
                        'type_msg' => $da['type_name'],
                        'info' => self::getinfo($ids = $da['vod_id'], $page = null, $type = $da['type_id'], $time = null, $api_num),
                    ];
                }
                break;
            case 1:
                if (empty($data['list']['video'][0])) {
                    $da = $data['list']['video'];
                    $new_data[] = [
                        'id' => $da['id'],
                        'title' => $da['name'],
                        'uptime' => $da['last'],
                        'type' => $da['tid'],
                        'type_msg' => $da['type'],
                        'info' => self::getinfo($ids = $da['id'], $page = null, $type = $da['type_id'], $time = null, $api_num),
                    ];
                } else {
                    foreach ($data['list']['video'] as $da) {
                        $new_data[] = ['id' => $da['id'], 'title' => $da['name'], 'uptime' => $da['last'], 'type' => $da['tid'], 'type_msg' => $da['type']];
                    }
                }

                break;
        }
        return $new_data;
    }

    /*详情*/
    public static function Chaeck_Info_data($api_num, $data)
    {
        $new_data = [];
        switch ($api_num) {
            case 0:
                $da = $data['list'][0];
                $arr = ['id' => $da['vod_id'], 'title' => $da['vod_name'], 'uptime' => $da['vod_time'], 'type' => $da['type_id'], 'type_msg' => $da['type_name']];
                $arr2 = ['img' => $da['vod_pic'], 'infor' => $da['vod_content']];
                $new_data = array_merge($arr, $arr2);
                foreach (explode('#', $da['vod_play_url']) as $index => $item) {
                    $array[explode('$', $item)[0]] = explode('$', $item)[1];
                }
                $new_data['link'] = $array;
                break;
            case 1:
                $da = $data['list']['video'];
                $arr = ['id' => $da['id'], 'title' => $da['name'], 'uptime' => $da['last'], 'type' => $da['tid'], 'type_msg' => $da['type']];
                $arr2 = ['img' => $da['pic'], 'infor' => is_array($da['des']) ? implode(',', $da['des']) : $da['des']];
                $new_data = array_merge($arr, $arr2);

                if (is_array($da['dl']['dd'])) {
                    for ($i = 0; $i < count($da['dl']['dd']); $i++) {
                        foreach (explode('#', $da['dl']['dd'][$i]) as $index => $item) {
                            if (empty($array[explode('$', $item)[0]])) {
                                $array[explode('$', $item)[0]] = explode('$', $item)[1];
                            } else {
                                $array[explode('$', $item)[0] . rand(1, 50)] = explode('$', $item)[1];
                            }
                        }
                    }
                    $new_data['link'] = $array;
                } else {
                    foreach (explode('#', $da['dl']['dd']) as $index => $item) {
                        $array[explode('$', $item)[0]] = explode('$', $item)[1];
                    }
                    $new_data['link'] = $array;
                }
                break;
        }
        return $new_data;
    }


}
