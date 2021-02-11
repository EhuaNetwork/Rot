<?php
function decode($str)
{
    $str = preg_replace_callback('/\\\u([0-9a-f]{4})/', function ($match) {
        return iconv('UCS-2BE', 'UTF-8', pack('H4', $match[1]));   //抓取来的ucs-2编码的信息转码成utf-8格式的
    }, $str);
    return $str;
}


function post($url, $data, $header)
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


function get($url)
{
    $header = [];
    $curl = curl_init();
    //设置抓取的url
    curl_setopt($curl, CURLOPT_URL, $url);
    //设置头文件的信息作为数据流输出
    curl_setopt($curl, CURLOPT_HEADER, 0);
    // 超时设置,以秒为单位
    curl_setopt($curl, CURLOPT_TIMEOUT, 1);

    // 超时设置，以毫秒为单位
    curl_setopt($curl, CURLOPT_TIMEOUT_MS, 5000);

    // 设置请求头
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    //执行命令
    $data = curl_exec($curl);

    // 显示错误信息
    if (curl_error($curl)) {
//        print "Error: " . curl_error($curl);
        return false;
    } else {
        // 打印返回的内容
        curl_close($curl);
        return $data;
    }

}

function x16($result)
{
    $result = str_replace("\x10", '', $result);
    $result = str_replace("\x11", '', $result);
    $result = str_replace("\x12", '', $result);
    $result = str_replace("\x13", '', $result);
    $result = str_replace("\x14", '', $result);
    $result = str_replace("\x15", '', $result);
    $result = str_replace("\x16", '', $result);
    $result = str_replace("\x17", '', $result);
    $result = str_replace("\x18", '', $result);
    $result = str_replace("\x19", '', $result);
    $result = str_replace("\x20", '', $result);
    return $result;
}

function x162($result)
{
    $result = preg_replace("/[\x{00}-\x{20}]/u", "", $result);
    return $result;
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

