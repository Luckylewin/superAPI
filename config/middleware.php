<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/8/31
 * Time: 17:35
 */
use App\Middleware\AuthMiddleware;
use App\Middleware\JsonMiddleware;
use \App\Middleware\TokenMiddleware;
use \App\Middleware\SignMiddleware;

return [
     // 全局中间件
    'global' => [
        //'App\Middleware\AppMiddleware'
    ],
    // 路由中间件
    'route' => [
        'auth' => AuthMiddleware::class,
        'json' => JsonMiddleware::class,
        'token' => TokenMiddleware::class,
        'sign' => SignMiddleware::class,
    ],
];