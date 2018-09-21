<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/3
 * Time: 10:46
 */

return [
    //阿里云OSS配置
    'OSS' =>[
        'ACCESS_ID'=> '',    //ID
        'ACCESS_KEY' => '', // KEY
        'ENDPOINT'=>'',//指定区域
        'BUCKET'=>'',//bucket
    ],

    'NGINX' => [
        'MEDIA_PORT' => '',
        'MEDIA_IP' => '',
        'DEFAULT_EXPIRE' => '3600',
        'DEFAULT_SECRET' => '',
    ],

    /*
     *  DokyPay 支付
     */
    'DOKYPAY' => [
        'APP_ID' => '',
        'APP_KEY' => '',
        'MERCHANT_ID' => '',
        'MERCHANT_KEY' => ''
    ],

    /*
     *  Paypal 支付
     */
    'PAYPAL' => [
        'CLIENT_ID' => '',
        'SECRET' => ''
    ]

];