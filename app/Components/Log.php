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

    public static function info(Request $request)
    {

        // 如果没有用标准的application/json流 进行请求
        if ($request->method() == 'POST' && isset($request->server()->HTTP_CONTENT_TYPE) && $request->server()->HTTP_CONTENT_TYPE != 'application/json') {
            $log = $request->rawData()->scalar;
        } else {

            if (count(get_object_vars($request->request())) > 0) {
                $log = (array) $request->request();
            } else {
                $log = (array) $request->get();
            }

            $log = json_encode($log);
        }

        if ($log == '[]' || empty($log)) {
            return false;
        }

        $redis = Redis::singleton();
        $redis->select(Redis::$REDIS_API_LOG);

        $now = date('H:i:s');
        $ip = $request->ip();
        $logStr = $now . '|' . $ip . '|' . $log ;
        $redis->lPush('log', $logStr);
        self::stdout($logStr);

        return true;
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