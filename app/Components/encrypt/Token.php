<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/10/16
 * Time: 14:22
 */

namespace App\Components\encrypt;


class Token
{
    // token 防盗链密钥
    private static $key = 'topthinker';

    /**
     * @param $path string URI 路径
     * @param $ip string ip
     * @param int $expire 过期时间
     * @return string
     */
    public static function generate($path, $ip , $expire = 600)
    {
        $expireTime = time() + $expire; // 授权 10 分钟后过期
        return substr(md5(self::$key.'&'.$expireTime.'&'.$path.'&'.$ip), 12, 8) . $expireTime;
    }

    /**
     * @param $sign string 链接的签名
     * @param $path string 资源路径
     * @param $ip string ip
     * @return bool
     */
    public static function validate($sign, $path, $ip)
    {
        $expireTime = substr($sign, 8, 10);

        if ($expireTime < time()) {
            return false;
        }

        $serverSign = substr(md5(self::$key.'&'.$expireTime.'&'.$path.'&'.$ip), 12, 8) . $expireTime;
        if ($serverSign != $sign) {
            return false;
        }

        return true;
    }
}