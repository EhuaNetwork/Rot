<?php


namespace App\Http\Controllers\API;


use Illuminate\Support\Facades\DB;

class T_yunhei
{

    static function yunhei($key,$qun,$qq)
    {
        self::yunhei_caiji($key,$qun,$qq);//云黑采集

        $rrr = DB::table('mode_yunhei')->where(['qq' => $qq, 'status' => 1])->first();
        $rrr = json_encode($rrr, 256);
        $rrr = json_decode($rrr, true);


        if (!empty($rrr)) {
            //管理员或群主  不bb
            $res = \QQROT\QQROT2::get_group_admin($qun);//查询群管理列表
            if (in_array($qq, $res)) {
                return false;
            }

            $text = "===检测到云黑成员信息===";
            $text .= "\nQQ:" . $rrr['qq'];
            $text .= "\n时间:" . $rrr['intime'];
            $text .= "\n风险级别:" . $rrr['level'] . '级';
//                $text .= "\n投诉人数:" . $res['num'] . '人';
            $text .= "\n拉黑事件:" . $rrr['body'];
            $text .= "\n[拉我Jin群，快速查云黑]\n[举报发送：举报QQ-原因]";
            $text .= "\n数据来源:" . $rrr['from'];

            \QQROT\QQROT2::send_group_msg($qun, $text, $anonymous = false, 'str');
            die;
        }

        if ($key == '云黑系统' || $key == '云黑') {
            $res = "===云黑系统===\n查云黑QQ\n举报QQ-原因\n云黑审核\n审核通过QQ\n审核拒绝QQ\n云黑删除QQ";
            \QQROT\QQROT2::send_group_msg($qun, $res, $anonymous = false, 'str');
        } elseif (preg_match("/查云黑\d+/", $key)) {
            $cha_qq = trim(str_replace('查云黑', '', $key));
            $res = DB::table('mode_yunhei')->where(['qq' => $cha_qq, 'status' => 1])->first();
            $res = json_encode($res, 256);
            $res = json_decode($res, true);
            if (!empty($res)) {
                $text = "===云黑信息===";
                $text .= "\nQQ:" . $res['qq'];
                $text .= "\n时间:" . $res['intime'];
                $text .= "\n风险级别:" . $res['level'] . '级';
//                $text .= "\n投诉人数:" . $res['num'] . '人';
                $text .= "\n拉黑事件:" . $res['body'];
                $text .= "\n[拉我进群，快速查云黑]\n[举报发送：举报QQ-原因]";
                $text .= "\n数据来源:" . $res['from'];

            } else {
                $text = "未收录该用户，与其交易也需小心。\n挂黑可以发送:举报QQ-内容";
            }
            \QQROT\QQROT2::send_group_msg($qun, $text, $anonymous = false, 'str');
        } elseif (preg_match("/举报\d+/", $key)) {

            if (preg_match("/举报\d+-/", $key, $cha_qq)) {
                $cha_qq = trim(str_replace('举报', '', $cha_qq[0]), '-');

                $yunhei_info = DB::table('mode_yunhei')->where(['qq' => $cha_qq])->first();
                $yunhei_info = json_encode($yunhei_info, 256);
                $yunhei_info = json_decode($yunhei_info, true);
                if (!empty($yunhei_info)) {
                    $body = str_replace("举报$cha_qq-", '', $key);
                    $level = $yunhei_info['level'] + 1;
                    DB::table('mode_yunhei')->where(['qq' => $cha_qq])->update(['level' => $level, 'body' => $body, 'intime' => date('Y-m-d H:i:s', time())]);
                    $text = "该用户已被多人投诉，现已升级风险等级为{$level}级";
                } else {
                    $body = str_replace("举报$cha_qq-", '', $key);
                    $data = [
                        'qq' => $cha_qq,
                        'level' => 1,
                        'body' => $body,
                        'num' => 1,
                        'intime' => date('Y-m-d H:i:s', time()),
                        'status' => 0,
                        'up_qq' => $qq,
                        'up_qun' => $qun,
                        'from' => '北海云黑',
                    ];

                    $text = '信息已上传，等待监管审核';
                    DB::table('mode_yunhei')->insert($data);
                }
            } else {
                $text = "信息格式有误\n格式：举报QQ-原因";
            }
            \QQROT\QQROT2::send_group_msg($qun, $text, $anonymous = false, 'str');
        } elseif (preg_match("/审核通过\d+/", $key)) {
            $cha_qq = trim(str_replace('审核通过', '', $key));
            if (DB::table('mode_yunhei_boss')->where(['qq' => $qq])->count() > 0) {
                $res = DB::table('mode_yunhei')->where(['qq' => $cha_qq])->update(['status' => 1, 'supervise' => $qq]);
                $text = '审核完成';
            } else {
                $text = '无权操作';
            }
            \QQROT\QQROT2::send_group_msg($qun, $text, $anonymous = false, 'str');
        } elseif (preg_match("/^云黑审核$/", $key)) {
            if (DB::table('mode_yunhei_boss')->where(['qq' => $qq])->count() > 0) {
                $res = DB::table('mode_yunhei')->where(['status' => 0])->limit(10)->get();
                $res = json_encode($res, 256);
                $res = json_decode($res, true);
                if (empty($res)) {
                    $text = '暂无待审';
                } else {
                    $max_num = count($res);
                    $rand = rand(0, ($max_num - 1));
                    $res = $res[$rand];
                    $qq = $res['qq'];
                    $text = "===云黑审核===";
                    $text .= "\nQQ:" . $qq;
                    $text .= "\n时间:" . $res['intime'];
                    $text .= "\n风险级别:" . $res['level'] . '级';
//                $text .= "\n投诉人数:" . $res['num'] . '人';
                    $text .= "\n拉黑事件:" . $res['body'];
                    $text .= "\n\n审核通过$qq\n审核拒绝$qq";
                }

            } else {
                $text = '无权操作';
            }
            \QQROT\QQROT2::send_group_msg($qun, $text, $anonymous = false, 'str');
        } elseif (preg_match("/审核拒绝\d+/", $key)) {
            $cha_qq = trim(str_replace('审核拒绝', '', $key));
            if (DB::table('mode_yunhei_boss')->where(['qq' => $qq])->count() > 0) {
                $res = DB::table('mode_yunhei')->where(['qq' => $cha_qq])->delete();
                $text = '审核完成';
            } else {
                $text = '无权操作';
            }
            \QQROT\QQROT2::send_group_msg($qun, $text, $anonymous = false, 'str');
        } elseif (preg_match("/云黑删除\d+/", $key)) {
            $cha_qq = trim(str_replace('云黑删除', '', $key));
            if (DB::table('mode_yunhei_boss')->where(['qq' => $qq])->count() > 0) {
                $res = DB::table('mode_yunhei')->where(['qq' => $cha_qq])->delete();
                $text = '删除完成';
            } else {
                $text = '无权操作';
            }
            \QQROT\QQROT2::send_group_msg($qun, $text, $anonymous = false, 'str');
        }


    }

    static function yunhei_caiji($key,$qun,$qq)
    {
//        dd($key);
        if (preg_match("/======Stars 云黑======/", $key)) {
            $from = 'Stars云黑';
            if (preg_match("/查询QQ:\d+/", $key, $cha_qq) &&
                preg_match("/黑名单等级:\d+级/", $key, $num) &&
                preg_match("/黑名单原因:(.*\s)+审核员/", $key, $body) &&
                preg_match("/黑名单时间:(.*)秒/", $key, $time) &&
                preg_match("/审核员:\d+/", $key, $super)) {
                $cha_qq = trim(str_replace("查询QQ:", '', $cha_qq[0]), "");
                $num = trim(str_replace("黑名单等级:", '', $num[0]), '级');
                $body = str_replace("\n审核员",'',trim(str_replace("黑名单原因:", '', $body[0]), ""));
                $time = trim(str_replace("黑名单时间:", '', $time[0]), "");
                $super = trim(str_replace("审核员:", '', $super[0]), "");
                $time = str_replace('日', ' ', $time);
                $time = str_replace('年', '-', $time);
                $time = str_replace('月', '-', $time);
                $time = str_replace('时', ':', $time);
                $time = str_replace('分', ':', $time);
                $time = str_replace('秒', '', $time);
                if (DB::table('mode_yunhei')->where('qq', $cha_qq)->count() > 0) {
                    return false;
                } else {

                    $data = [
                        'qq' => $cha_qq,
                        'level' => $num,
                        'body' => $body,
                        'num' => $num,
                        'intime' => $time,
                        'status' => 1,
                        'up_qq' => $qq,
                        'up_qun' => $qun,
                        'supervise' => $super,
                        'from' => $from,
                    ];
                    DB::table('mode_yunhei')->insert($data);
                }

            } else {
                return false;
            }
            die;
        }


        if (preg_match("/=====检查到以下云黑====/", $key)) {
            $from = '柚云黑';
            if (preg_match("/QQ号:\d+/", $key, $cha_qq) &&
                preg_match("/云黑等级:[A-Z]+/", $key, $num) &&
                preg_match("/云黑原因:.*/", $key, $body)) {

                $cha_qq = trim(str_replace("QQ号:", '', $cha_qq[0]), "");
                $num = trim(str_replace("云黑等级:", '', $num[0]), '');
                $body = trim(str_replace("云黑原因:", '', $body[0]), "");
                if (DB::table('mode_yunhei')->where('qq', $cha_qq)->count() > 0) {
                    return false;
                } else {
                    $data = [
                        'qq' => $cha_qq,
                        'level' => 1,
                        'body' => $body,
                        'num' => 1,
                        'intime' => date('Y-m-d H:i:s', time()),
                        'status' => 1,
                        'up_qq' => $qq,
                        'up_qun' => $qun,
                        'supervise' => 1963806765,
                        'from' => $from,
                    ];
                    DB::table('mode_yunhei')->insert($data);
                }

            } else {
                return false;
            }
            die;
        }


    }
}
