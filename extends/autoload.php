<?php
function decode($str)
{
    $str = preg_replace_callback('/\\\u([0-9a-f]{4})/', function ($match) {
        return iconv('UCS-2BE', 'UTF-8', pack('H4', $match[1]));   //抓取来的ucs-2编码的信息转码成utf-8格式的
    }, $str);
    return $str;
}


/**guzz网络请求 get post
 * @param null $url 请求地址
 * @param string $mothod 请求模式
 * @param array $field 参数
 * @param array $header 请求头
 * @param int $outtime 超时
 * @return string
 * @throws \GuzzleHttp\Exception\GuzzleException
 * @author Ehua(ehua999@163.com)
 * @date 2020/9/17 9:02
 */
function guzz($url = null, $field = [], $mothod = 'get', $header = [], $outtime = 3)
{
    if (is_array($field)) {
        $type = 'form_params';
    } else {
        $field = json_decode($field);
        $type = 'json';
    }

    $client = new GuzzleHttp\Client(); //初始化客户端

    if ($mothod == 'get') {
        $response = $client->get($url, [
            'query' => $field,
            'header' => $header,
            'timeout' => $outtime, //设置请求超时时间
            'verify' => false,
        ]);
    } else {
        $response = $client->request('POST', $url, [
            $type => $field,
            'header' => $header,
            'timeout' => $outtime,
            'verify' => false,
        ]);
    }

    $body = $response->getBody(); //获取响应体，对象
    $bodyStr = (string)$body; //对象转字串,这就是请求返回的结果
    return $bodyStr;
}
