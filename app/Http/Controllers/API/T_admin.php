<?php


namespace App\Http\Controllers\API;


use Illuminate\Support\Facades\DB;

class T_admin
{
    static function init($key, $qun, $qq)
    {
        if ($key == '群管系统' || $key == '群管') {
            $res =
                "===群管系统===
踢[QQ]
禁言[QQ] [分钟]
解禁[QQ]
上管理[QQ]
下管理[QQ]
发公告[标题]-[内容]
全体禁言
全体解禁";
            \QQROT\QQROT2::send_group_msg($qun, $res, $anonymous = false, 'str');
            die;
        }

        if (preg_match("/踢\[@\d+\]/", $key, $cha_qq) || (preg_match("/踢\d+/", $key, $cha_qq))) {
            $cha_qq = str_replace("[@", '', $cha_qq[0]);
            $cha_qq = str_replace("]", '', $cha_qq);
            $cha_qq = str_replace("踢", '', $cha_qq);
            if (!self::getrotauth($qun)) {
                \QQROT\QQROT2::send_group_msg($qun, '我还不是管理员', $anonymous = false, 'str');
                die;
            }
            if (!self::getauth($qun, $qq)) {
                \QQROT\QQROT2::send_group_msg($qun, '无权操作', $anonymous = false, 'str');
                die;
            }

            if (\QQROT\QQROT2::delete_member($qun, $cha_qq)) {
                $str = '操作完毕';
            } else {
                $str = '操作失败';

            }
            \QQROT\QQROT2::send_group_msg($qun, $str, $anonymous = false, 'str');
            die;
        }

        if (preg_match("/禁言\[@\d+\] \d+/", $key, $data) || (preg_match("/禁言\d+ \d+/", $key, $data))) {
            $data = str_replace("[@", '', $data[0]);
            $data = str_replace("]", '', $data);
            $data = str_replace("禁言", '', $data);

            $cha_qq = explode(' ', $data)[0];
            $time = explode(' ', $data)[1];

            if (!self::getrotauth($qun)) {
                \QQROT\QQROT2::send_group_msg($qun, '我还不是管理员', $anonymous = false, 'str');
                die;
            }
            if (!self::getauth($qun, $qq)) {
                \QQROT\QQROT2::send_group_msg($qun, '无权操作', $anonymous = false, 'str');
                die;
            }

            if (\QQROT\QQROT2::ban($qun, $cha_qq, $time * 60)) {
                $str = '操作完毕';
            } else {
                $str = '操作失败';

            }
            \QQROT\QQROT2::send_group_msg($qun, $str, $anonymous = false, 'str');
            die;
        }


        if (preg_match("/解禁\[@\d+\]/", $key, $cha_qq) || (preg_match("/解禁\d+/", $key, $cha_qq))) {
            $cha_qq = str_replace("[@", '', $cha_qq[0]);
            $cha_qq = str_replace("]", '', $cha_qq);
            $cha_qq = str_replace("解禁", '', $cha_qq);
            if (!self::getrotauth($qun)) {
                \QQROT\QQROT2::send_group_msg($qun, '我还不是管理员', $anonymous = false, 'str');
                die;
            }
            if (!self::getauth($qun, $qq)) {
                \QQROT\QQROT2::send_group_msg($qun, '无权操作', $anonymous = false, 'str');
                die;
            }

            if (\QQROT\QQROT2::ban($qun, $cha_qq, 0)) {
                $str = '操作完毕';
            } else {
                $str = '操作失败';

            }
            \QQROT\QQROT2::send_group_msg($qun, $str, $anonymous = false, 'str');
            die;
        }
        if ((preg_match("/发公告.*-.*/", $key, $data))) {

            $data = str_replace("发公告", '', $data[0]);
            $title = explode('-', $data)[0];
            $content = explode('-', $data)[1];
            $pic = @explode('-', $data)[2];
            if (!self::getrotauth($qun)) {
                \QQROT\QQROT2::send_group_msg($qun, '我还不是管理员', $anonymous = false, 'str');
                die;
            }
            if (!self::getauth($qun, $qq)) {
                \QQROT\QQROT2::send_group_msg($qun, '无权操作', $anonymous = false, 'str');
                die;
            }
            \QQROT\QQROT2::set_group_notice($qun, $title, $content,$pic);
        }
//        dd($key);
        if (preg_match("/下管理\[@\d+\]/", $key, $cha_qq) || (preg_match("/下管理\d+/", $key, $cha_qq))) {
            $cha_qq = str_replace("[@", '', $cha_qq[0]);
            $cha_qq = str_replace("]", '', $cha_qq);
            $cha_qq = str_replace("下管理", '', $cha_qq);
            if (!self::getrotauth($qun)) {
                \QQROT\QQROT2::send_group_msg($qun, '我还不是群主', $anonymous = false, 'str');
                die;
            }
            if (!self::getauth($qun, $qq)) {
                \QQROT\QQROT2::send_group_msg($qun, '无权操作', $anonymous = false, 'str');
                die;
            }

            if (\QQROT\QQROT2::set_group_admin($qun, $cha_qq, true)) {
                $str = '操作完毕';
            } else {
                $str = '操作失败';

            }
            \QQROT\QQROT2::send_group_msg($qun, $str, $anonymous = false, 'str');
            die;
        }

        if (preg_match("/上管理\[@\d+\]/", $key, $cha_qq) || (preg_match("/上管理\d+/", $key, $cha_qq))) {
            $cha_qq = str_replace("[@", '', $cha_qq[0]);
            $cha_qq = str_replace("]", '', $cha_qq);
            $cha_qq = str_replace("上管理", '', $cha_qq);
            if (!self::getrotauth($qun)) {
                \QQROT\QQROT2::send_group_msg($qun, '我还不是群主', $anonymous = false, 'str');
                die;
            }
            if (!self::getauth($qun, $qq)) {
                \QQROT\QQROT2::send_group_msg($qun, '无权操作', $anonymous = false, 'str');
                die;
            }

            if (\QQROT\QQROT2::set_group_admin($qun, $cha_qq, false)) {
                $str = '操作完毕';
            } else {
                $str = '操作失败';

            }
            \QQROT\QQROT2::send_group_msg($qun, $str, $anonymous = false, 'str');
            die;
        }


        if (preg_match("/全体禁言/", $key, $cha_qq)) {
            if (!self::getrotauth($qun)) {
                \QQROT\QQROT2::send_group_msg($qun, '我还不是管理员', $anonymous = false, 'str');
                die;
            }
            if (!self::getauth($qun, $qq)) {
                \QQROT\QQROT2::send_group_msg($qun, '无权操作', $anonymous = false, 'str');
                die;
            }

            \QQROT\QQROT2::mute($qun, 'true');
            die;
        }
        if (preg_match("/全体解禁/", $key, $cha_qq)) {
            if (!self::getrotauth($qun)) {
                \QQROT\QQROT2::send_group_msg($qun, '我还不是管理员', $anonymous = false, 'str');
                die;
            }
            if (!self::getauth($qun, $qq)) {
                \QQROT\QQROT2::send_group_msg($qun, '无权操作', $anonymous = false, 'str');
                die;
            }

            \QQROT\QQROT2::mute($qun, 'false');
            die;
        }
    }

    static function getauth($qun, $qq)
    {
        if (DB::table('q_system')->where('value', 'like', "%$qq%")->count() > 0) {
            return true;
        }

        $arr = \QQROT\QQROT2::get_group_admin($qun);
        if (in_array($qq, $arr)) {
            return true;
        } else {
            return false;
        }
    }

    static function getrotauth($qun)
    {
        $arr = \QQROT\QQROT2::get_group_admin($qun);
        if (in_array(config('QQROT.qq'), $arr)) {
            return true;
        } else {
            return false;
        }
    }


}
