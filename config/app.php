<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/8/31
 * Time: 17:35
 */

return [
    'server' => [
        'hostname' => '192.168.0.11',
        'ip' => '192.168.0.11',
        'port' => '12389'
    ],
    /*
    |--------------------------------------------------------------------------
    | HTTP 响应内容压缩
    |--------------------------------------------------------------------------
    |
    | Set compress options you can change the HTTP Response Header 'Content-Encoding'.
    |
    | encoding: encoding type of Content-Encoding
    | level: encoding level, from 1 to 9
    | content_type: content type you want to compress
    |
    */
    'compress' => [
        'encoding'     => 'gzip', // If you don't want to compress, set it to '' (gzip | deflate | '')
        'level'        => '5',
        'content_type' => [
            'application/json',
            'text/html',
        ],
    ],
];