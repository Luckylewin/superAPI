<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/4/8
 * Time: 16:28
 */

namespace App\Service;

use App\Components\cache\Redis;
use App\Components\Validator;
use App\Exceptions\ErrorCode;
use Breeze\Config;
use Breeze\Http\Request;

class common
{
    public $uid;
    public $data;
    public $redis;

    public function __construct(Request $request)
    {
        $this->uid = $request->post('uid');
        $this->data = $request->post('data');
    }

    /**
     * 处理raw post数据
     * @param null $field
     * @param null $default
     * @param null $rule
     * @return null|object|string|integer
     * @throws \Exception
     */
    public function post($field = null, $default = null, $rule = null)
    {
        if (!$field && !$default) {
            return $this->data;
        }
        if (!isset($this->data[$field]) && is_null($default)) {
            throw new \InvalidArgumentException($field . "是必须的参数", ErrorCode::$RES_ERROR_PARAMETER_MISSING);
        }

        if (isset($this->data[$field])) {
            $val = $this->data[$field];
            if ($rule) {
                $result =  Validator::validate($rule, $val);
                if ($result['status'] === false) {
                    throw new \InvalidArgumentException("参数错误", $result['code']);
                }
                return $result['value'];
            }
            return $val;

        } else if ($default) {
            return $val = $default;
        }

        return null;
    }

    //计算总页数
    public static function getTotalPage($perPage, $totalPage)
    {
        if (!$totalPage) {
            return 0;
        }
        if ($perPage <= 0 || !is_numeric($perPage)) {
            $perPage = 10;
        }
        return ceil( (int)$totalPage / (int)$perPage);
    }

    public function formatMac($uid)
    {
        return str_replace(':', '', $uid);
    }

    public function checkMac($uid)
    {
        return true;
    }

    public function getRedis($db = null)
    {
        $cache = Redis::singleton();
        if ($db) {
            $cache->getRedis()->select($db);
        }

        return $cache;
    }

    /**
     * nginx防盗链
     * @param $mac
     * @param $path
     * @param int $expireTime
     * @return string
     * @throws \Exception
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

    public function setTimeZone($timezone)
    {
        $offset = strpos($timezone, '-') !== false ? "+" . abs(trim($timezone, '-')) : "-" . abs(trim($timezone, '+'));
        date_default_timezone_set("Etc/GMT{$offset}");
    }

    public function judgeIsNeedUpdate($client,$server)
    {
        if (is_null($client)) {
            return true;
        }
        $clientVersion = ltrim(strtolower($client),'v');
        $serverVersion = ltrim(strtolower($server),'v');
        $res = strnatcmp($serverVersion,$clientVersion);
        if ($res == 1) {
            return true;
        }else{
            return false;
        }

    }

    /**
     * 从缓存中取数据
     * @param $key
     * @param $db
     * @return array|bool
     */
    public function getDataFromCache($key, $db)
    {
        $cacheKey = $key;
        $this->redis = $this->getRedis($db);
        if ($cacheValue = $this->redis->get($cacheKey)) {
            $cacheValue =  json_decode($cacheValue, true);

            return $cacheValue;
        }

        return false;
    }


}