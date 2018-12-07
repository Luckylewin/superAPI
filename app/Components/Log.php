<?php
namespace App\Components;

use App\Components\cache\Redis;
use App\Components\helper\FileHelper;
use Breeze\Http\Request;

class Log
{
    public static $total;

    public static function write($file, $str, $append = true)
    {
        if (is_writable($file) == false) {
            FileHelper::createFile($file);
        }

        if ($str) {
            $str = date('Y-m-d H:i:s') . "|" . $str;
            file_put_contents($file, $str, $append ? FILE_APPEND : null);
        }
    }

    protected static function getLog(Request $request)
    {
        // 如果没有用标准的application/json流 进行请求
        if ($request->method() == 'POST' && isset($request->server()->HTTP_CONTENT_TYPE) && $request->server()->HTTP_CONTENT_TYPE != 'application/json') {
            return $request->rawData()->scalar;
        }

        if (count(get_object_vars($request->request())) > 0) {
            $log = (array) $request->request();
        } else {
            $log = (array) $request->get();
        }

        if (!isset($log['header'])) {
            $uri = $request->server('REQUEST_URI');
            if (strpos($uri, '?') !== false) {
                $log['header'] = ltrim(strstr($uri, '?', true), '/');
            } else {
                $log['header'] = strstr(ltrim($uri, '/'), '/', true);
            }
        }

        $log = json_encode($log);
        if ($log == '[]' || empty($log)) {
            return false;
        }

        return $log;
    }

    public static function info(Request $request, $error=null)
    {
        if ($log = self::getLog($request)) {
            $redis = Redis::singleton();
            $redis->select(Redis::$REDIS_API_LOG);

            $now = date('H:i:s');
            $ip = $request->ip();
            $logStr = $now . '|' . $ip . '|' . $log ;
            if ($error) {
                $logStr .= '|ERROR:'.$error;
            }

            $redis->lPush('log', $logStr);
            self::stdout($logStr);
        }
    }

    public static function stdout($str)
    {
        if (ENV == 'dev') {
            echo $str , PHP_EOL;
        }
    }

    public static function notifyLog($payType, $message)
    {
        $file = dirname(__DIR__) . '/../storage/logs/' . $payType . '_notify.log' ;
        if (!file_exists($file)) {
            touch($file);
        }

        file_put_contents($file, date('Y-m-d H:i:s') . '  '. $message  . PHP_EOL, FILE_APPEND);
    }

}