<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/3
 * Time: 10:51
 */

namespace App\Components\helper;


class TimeHelper
{
    /**
     * 时间戳转换
     * @param $datetime string 指定的时间
     * @param $format string 指定的格式
     * @param $toTimeZone integer 目标时区
     * @param $fromTimeZone integer 源时区
     * @return false|string
     */
    public static function convertTimeZone($datetime, $format ,$toTimeZone, $fromTimeZone = 8)
    {
        $fromTimeZone = -$fromTimeZone;
        $timeZone = $fromTimeZone > 0 ? "Etc/GMT+{$fromTimeZone}" : "Etc/GMT{$fromTimeZone}";

        date_default_timezone_set($timeZone);

        //求出源时间戳
        $from_time = strtotime($datetime);

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
}