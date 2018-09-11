<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/3
 * Time: 13:16
 */

namespace App\Components\helper;

use App\Exceptions\ErrorCode;
use Breeze\Config;

class Func
{
    /**
     * nginx防盗链
     * @param $mac
     * @param $path
     * @param int $expireTime
     * @return string
     */
    public static function getAccessUrl($mac, $path, $expireTime = 300)
    {
        if (empty($path)) {
            return 'null';
        } elseif (strpos($path, 'http') !== false) {
            return $path;
        }

        $config =  Config::get('params.NGINX');
        $url = "http://" . $config['MEDIA_IP'] . ":" . $config['MEDIA_PORT'] . $path . "?";
        $secret = $config['DEFAULT_SECRET'] ; //加密密钥
        $expire = time() + $expireTime;//链接有效时间
        $md5 = md5($secret.$expire, true); //生成密钥与过期时间的十六位二进制MD5数，
        $md5 = base64_encode($md5);// 对md5进行base64_encode处理
        $md5 = str_replace(array('=','/','+'), array('','_','-'), $md5); //分别替换字符，去掉'='字符, '/'替换成'_','+'替换成'-'
        $key = md5($secret.$mac.$path); //对密钥, mac地址,芯片序列号sn,资源路径path进行MD5处理
        $url .= "st={$md5}&e={$expire}&key={$key}&mac={$mac}";//最后拼接

        return $url;
    }

    /**
     * 计算本地时间
     * @param $toTimeZone
     * @param string $format
     * @param null $timestamp
     * @return false|null|string
     */
    public function getLocalTime($toTimeZone, $format = 'Y-m-d', $timestamp = null)
    {
        date_default_timezone_set("Etc/GMT+0");
        $fromTimeZone = 0;
        //求出源时间戳
        $from_time = $timestamp;

        //求出两者的差值
        $diff = $fromTimeZone > $toTimeZone ? $fromTimeZone - $toTimeZone : $toTimeZone - $fromTimeZone;
        $diff = $diff * 3600;

        $toTimeZone = $fromTimeZone > $toTimeZone ? $from_time - $diff : $from_time + $diff;

        if ($format == 'timestamp') {
            return $from_time;
        } else {
            return date($format, $toTimeZone);
        }

    }

    /**
     * json校验
     * @param $string
     * @return bool|object
     */
    public static function JsonValidate($string)
    {
        $result = @json_decode($string, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            if ($string === '' || $string === null) {
                return $result;
            }
            return false;
        }

        return $result;
    }

    public static function object_array($array)
    {
        if (is_object($array)) {
            $array = (array)$array;
        }

        if (is_array($array)) {
            foreach($array as $key=>$value) {
                $array[$key] = self::object_array($value);
            }
        }
        return $array;
    }

}