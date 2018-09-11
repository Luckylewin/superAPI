<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/8/31
 * Time: 11:18
 */

namespace Breeze;


use Breeze\Helpers\DotTool;

class Config
{
    protected static $config;

    public static function load($file, $conf)
    {
        self::$config[$file] = $conf;
    }

    public static function get($key, $default = null)
    {
        return DotTool::get(self::$config, $key, $default);
    }

    // TO DO
    public static function set()
    {

    }


}