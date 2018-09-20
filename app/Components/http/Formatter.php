<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/3
 * Time: 10:56
 */

namespace App\Components\http;
use App\Exceptions\ErrorCode;
use Breeze\Http\Response;
use Workerman\Protocols\HttpCache;

class Formatter
{
    public static $header;

    const JSON = 'json';
    const TEXT = 'text';
    const XML = 'xml';

    public static function format($format)
    {
        switch ($format)
        {
            case self::JSON:
                HttpCache::$header['Content-Type'] = 'Content-Type: application/json;charset=utf-8';
                break;
            case self::XML:
                HttpCache::$header['Content-Type'] = 'Content-Type: application/xml;charset=utf-8';
                break;
            default:
                HttpCache::$header['Content-Type'] = 'Content-Type: text/html;charset=utf-8';
        }

    }

    public static function success($data,$field = array(), $format = 'json')
    {
        static::setFormat($format);
        $header['header'] = self::$header;
        $header['error'] = 'success';
        if (!empty($field)) {
            foreach ($field as $k => $v) {
                $header[$k] = $v;
            }
        }
        $header['data'] = $data;

        return json_encode($header);
    }

    public static function response($code, $format = 'json')
    {
        static::setFormat($format);
        $data['error'] = ErrorCode::getError($code);
        $data['header'] = self::$header;//增加头
        $data['code'] = "10" . $code;

        return json_encode($data);
    }

    // 设置响应格式
    public static function setFormat($format)
    {
        if ($format == self::JSON) {
            Response::format(Response::JSON);
        } else if ($format == self::XML) {
            Response::format(Response::XML);
        } else {
            Response::format(Response::TEXT);
        }

    }

    public static function setHeader($action)
    {
        self::$header = trim($action, '/');
    }

    public static function getNormalView($new_data)
    {
        $data['header'] = self::$header;//增加头
        $data['error'] = "success";
        $data['data'] = $new_data;

        return json_encode($data);
    }

    public static function back($data, $code)
    {
        static::setFormat(self::JSON);
        $header['code'] = "10" . $code;
        $header['msg'] = ErrorCode::getError($code);
        $header['data'] = $data;

        return json_encode($header);
    }

}