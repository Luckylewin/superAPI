<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/8/31
 * Time: 17:35
 */

return [
    'db' => [
        'driver'    => 'mysql',
        'host'      => '127.0.0.1',
        'database'  => '',
        'username'  => '',
        'password'  => '',
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],

    //缓存配置
    'redis' => [
        'host'=>'127.0.0.1',    //服务器地址
        'port' => '6379', // 端口
        'password' => '', // 密码
    ],
];