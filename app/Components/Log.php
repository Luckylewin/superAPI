<?php
namespace App\Components;

use App\Components\cache\Redis;
use Breeze\Http\Request;

class Log
{
    public static $total;

    public static function write(Request $request)
    {
        // 如果没有用标准的application/json流 进行请求
        if (isset($request->server()->HTTP_CONTENT_TYPE) && $request->server()->HTTP_CONTENT_TYPE != 'application/json') {
            $log = $request->rawData()->scalar;
        } else {
            $log = (array) $request->request();
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
        echo $str , PHP_EOL;
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