<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/8/31
 * Time: 11:37
 */

namespace Breeze\Helpers;

/**
 * 使用 a.b.c 方式存取配置数组
 * Class DotTool
 * @package Breeze\Helpers
 */

class DotTool
{
    /**
     * @param $array array 数组
     * @param $key string 点号分割
     * @param $default mixed 默认值
     * @return mixed
     */
    public static function get($array, $key, $default)
    {
        if (is_null($key)) {
            return $array;
        }

        if (isset($array[$key])) {
            return $array[$key];
        }

        $keyArr = explode('.', $key);
        foreach ($keyArr as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }

            $array = $array[$segment];
        }

        return $array;
    }

    public static function set()
    {

    }

    /**
     * 判断key是否存在
     * @param $array
     * @param $key
     * @return bool
     */
    public static function has($array, $key)
    {
        if (!is_array($array)) {
            return false;
        }

        if (array_key_exists($key, $array)) {
            return true;
        }

        $keyArr = explode('.', $key);
        foreach ($keyArr as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return false;
            }

            $array = $array[$segment];
        }

        return false;
    }
}