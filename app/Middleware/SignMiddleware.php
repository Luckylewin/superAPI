<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/20
 * Time: 11:20
 */

namespace App\Middleware;

use App\Components\encrypt\Token;
use Breeze\Http\Request;
use Breeze\Http\MiddlewareInterface;
use Workerman\Protocols\Http;

/**
 * Sign 加密签名 校验中间件
 * Class TokenMiddleware
 * @package App\Middleware
 */
class SignMiddleware implements MiddlewareInterface
{
    public function handle(Request $request)
    {
        
        $sign = $request->get('sign');
        $path = strstr($request->server('REQUEST_URI'), '?', true);

        $result = Token::validate($sign, $path, $request->ip());

        if ($result == true || $request->get('noauth')) {
            return $request;
        }

        return Http::header('location:http://img.ksbbs.com/asset/Mon_1703/05cacb4e02f9d9e.mp4');
    }
}