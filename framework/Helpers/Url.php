<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/10
 * Time: 11:45
 */

namespace Breeze\Helpers;


use Breeze\Config;

class Url
{
    public static function to($route, $params = [])
    {
        $config = Config::get('app.server');

        if ((isset($config['hostname']) || isset($config['ip'])) && isset($config['port'])) {
            $host = isset($config['hostname']) ? $config['hostname'] : $config['ip'];
            $port = isset($config['port']) ? $config['port'] : '80';
            $basic = "http://{$host}:{$port}/{$route}";
        } else {
            $basic = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $route;
        }

        if (!empty($params)) {
            $queryString = '';
            foreach ($params as $param => $value) {
                 $queryString .= "{$param}={$value}&";
            }
            $queryString = rtrim($queryString, '&');

            return $basic . '?' . $queryString;
        } else {
            return $basic;
        }
    }

}