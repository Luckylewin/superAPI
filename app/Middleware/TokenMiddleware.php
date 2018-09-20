<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/20
 * Time: 11:20
 */

namespace App\Middleware;

use App\Components\http\Formatter;
use App\Exceptions\ErrorCode;
use Breeze\Http\Request;
use Breeze\Http\MiddlewareInterface;
use App\Service\authService;

/**
 * Token 校验中间件
 * Class TokenMiddleware
 * @package App\Middleware
 */
class TokenMiddleware implements MiddlewareInterface
{
    public function handle(Request $request)
    {
        $access_token = $request->get('access-token');

        $result = (new authService($request))->validateAccessToken($access_token);

        if ($result == true) {
            return $request;
        }

        return Formatter::back(null,ErrorCode::$RES_ERROR_INVALID_REQUEST);
    }

}