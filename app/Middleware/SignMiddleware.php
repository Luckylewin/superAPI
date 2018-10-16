<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/20
 * Time: 11:20
 */

namespace App\Middleware;

use App\Components\encrypt\Token;
use App\Components\http\Formatter;
use App\Exceptions\ErrorCode;
use Breeze\Http\Request;
use Breeze\Http\MiddlewareInterface;

/**
 * Token 校验中间件
 * Class TokenMiddleware
 * @package App\Middleware
 */
class SignMiddleware implements MiddlewareInterface
{
    public function handle(Request $request)
    {
        $sign = $request->get('sign');
        $path = '/play/' . $request->get('name');
        
        $result = Token::validate($sign, $path, $request->ip());

        if ($result == true || $request->get('noauth')) {
            return $request;
        }

        return Formatter::back(null,ErrorCode::$RES_ERROR_LINK_EXPIRED);
    }

}