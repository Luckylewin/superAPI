<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/3
 * Time: 15:37
 */

namespace App\Middleware;

use App\Components\helper\Func;
use App\Exceptions\ErrorCode;
use Breeze\Http\MiddlewareInterface;
use Breeze\Http\Request;
use App\Components\http\Formatter;

/**
 * Json 校验中间件
 * Class JsonMiddleware
 * @package App\Middleware
 */
class JsonMiddleware implements MiddlewareInterface
{
    public function handle(Request $request)
    {
        // 判断是否为合法的json请求
        $rawData = $request->rawData()->scalar;
        $result = Func::JsonValidate($rawData);
        if ($result === false) {
            return function() {
                return Formatter::response(ErrorCode::$RES_ERROR_MESSAGE_IS_NOT_JSON);
            };
        }

        // 判断是否存在UID字段
        if (!isset($result['uid']) || !isset($result['header'])) {
            return function() {
                return Formatter::response(ErrorCode::$RES_ERROR_HEADER_OR_UID_NOT_SET);
            };
        }
        	
	$server = $request->server();
	if (isset($request->HTTP_CONTENT_TYPE) && $request->HTTP_CONTENT_TYPE != 'application/json' || !empty($rawData)) {
            $request->setPost($result);
            $request->setRequest($result);
        }


        return $request;
    }

}
