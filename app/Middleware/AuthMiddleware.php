<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/3
 * Time: 11:12
 */

namespace App\Middleware;

use App\Components\http\Formatter;
use Breeze\Http\MiddlewareInterface;
use Breeze\Http\Request;
use App\Service\authService;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request)
    {
        echo "Auth中间件执行" . PHP_EOL;

        $mac = $request->post()->uid;
        $data = $request->post()->data;
        $token = isset($data['token']) ? $data['token'] : null;

        if (strpos($token, '-') !== false) {
            $result = (new authService($request))->validateTokenViaYii($mac,$token);
        } else {
            $result = (new authService($request))->validateToken($mac,$token);
        }

         if ($result['status'] === false) {
             return function() use ($result) {
                 return Formatter::response($result['code']);
             };
         }

         return $request;
    }

}