<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/8/31
 * Time: 17:35
 */
use App\Middleware\AuthMiddleware;
use App\Middleware\JsonMiddleware;

return [
     // 全局中间件
    'global' => [
        //'App\Middleware\AppMiddleware'
    ],
    // 路由中间件
    'route' => [
        'auth' => AuthMiddleware::class,
        'json' => JsonMiddleware::class
    ],
];