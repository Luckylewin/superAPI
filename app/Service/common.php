<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/4/8
 * Time: 16:28
 */

namespace App\Service;

use App\Components\cache\Redis;
use App\Components\helper\Func;
use App\Components\Validator;
use App\Exceptions\ErrorCode;
use Breeze\Http\Request;

class common
{
    public $uid;
    public $data;
    public $redis;
    public $request;

    public function __construct(Request $request)
    {
        $this->uid = $request->post('uid');
        $this->data = $request->post('data');
        $this->request = $request;
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


    public function formatMac($uid)
    {
        return str_replace(':', '', $uid);
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
        } else{
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

    // 控制台输出
    public function stdout($str, $status)
    {
        if (ENV == 'dev') {
            echo Func::color($str, $status) . PHP_EOL;
        }
    }


}