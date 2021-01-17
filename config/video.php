<?php
$url = 'http://video.ehua.pro/';
return [
    //接口地址
    'epojie' => [
//        'api_domain'=>'http://epojie.ehua.pro/',
//        'api_domain'=>'http://ehua.pro,
        'api_domain' => $url,
    ],
    //第三方接口
    'public' => [
        'api_headimg' => "https://api.uomg.com/api/rand.avatar?sort=男&format=json",
    ],
    //闲聊么api
    'xianliaome' => [
        'ID' => '16233',
        "URL" => "https://www.xianliao.me/",
        'SSO_KEY ' => 'ZhtWDVrQxYrYdhZjjlZ91VNm4foygz7X',

    ],
    //公众号配置
    'wx_public' => [
        'AppID' => 'wx8fb907daa8077001',
        'AppSecret' => '02c09f2c26a54a98e42def48897cf543',
        'Token' => 'ehua',
        'EncodingAESKey' => 'ZPO8t6WC0yATFbJ8bZajZ4F3phkuPiuND5f1FxFCYuU',
    ],
    //video 采集地址
    'video' => [
        'cj' => [
            "https://api.okzy.tv/api.php/provide/vod/at/json",   //ok资源网    //ac=list&t=类别&pg=页码&wd=搜索关键字&h=几小时内的数据  //ac=detail&ids=数据ID&t=类型ID&pg=页码&h=几小时内的数据
            "http://zy.itono.cn/inc/api.php",                   //1717资源网   // ac= &t=类别&pg=页码&wd=搜索关键字&h=几小时内的数据    //ac=videolist&t=类型ID  &pg=页码 &h=几小时内的数据 &ids= 数据ID &wd=搜索关键字
        ],
        'jx' => [
//            "https://htoo.vip/owo/xxba/1314/m3u8.php?url=",
//            "http://jx.drgxj.com/?url=",
//            "https://www.1717yun.com/jiexi/?url=",
            "https://jx.idc126.net/jx/?url=",
//            "https://www.ikukk.com/js/jx/?url="
//            "https://apiman.pjyjw.com/mppm.php?url="
        ],
    ],


    'web' => [
        0=>"https://movie.lingtings.com/search.php?wd=",//vip影院
        1=>"https://www.kpkuang.com/vodsearch/-------------.html?wd=",//看片狂人
//        "http://002ka.cn/index.php/vod/search.html?wd=",//飘渺
        3=>"http://123456.li/search?keyword=",//video.tf
        '动漫'=>"https://www.agefans.tv/search?query="//动漫
    ],

    // 视图输出字符串内容替换
    'view_replace_str' => [
        '__api_domain__' => $url,
    ],
];
