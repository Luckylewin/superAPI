<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/8/31
 * Time: 15:02
 */

namespace Breeze\Http;

use Breeze\Di\Container;
use Breeze\Helpers\Pipeline;

class Middleware
{
    public static function run(Array $middlewares, Request $request)
    {
        // if middlewares empty, return request as is
        if (empty($middlewares)) {
            return $request;
        }

        $pipes = [];
        foreach ($middlewares as $middleware) {
            $middlewareInstance = Container::getInstanceWithSingleton($middleware);
            $pipes[] = [$middlewareInstance, 'handle'];
        }
        
        $pipeline = new Pipeline($pipes);
        
        return $pipeline->flow($request);
    }
}