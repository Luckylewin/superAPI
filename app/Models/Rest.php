<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/11/21
 * Time: 10:30
 */

namespace App\Models;

use Breeze\Helpers\Url;

class Rest
{
    /**
     * 设置资源描述
     * @param $meta
     * @param $route
     * @param $params
     * @param $pageField
     * @return array
     */
    public static function setLinks($meta, $route, $params, $pageField='page')
    {
        if ($params[$pageField] == 1) {
            if ($meta['pageCount'] == 1) {
                return [
                    'self' => self::setSelf($route,$params)
                ];
            } else {
                return [
                    'self' => self::setSelf($route, $params),
                    'next' => self::setNext($route, $params, $pageField),
                    'last' => self::setLast($route, $params, $meta, $pageField)
                ];
            }
        } else if ($params[$pageField] < $meta['totalCount']) {
            return [
                'self' => self::setSelf($route, $params),
                'first' => self::setFirst($route, $params, $pageField),
                'prev' => self::setPrev($route, $params, $pageField),
                'next' => self::setNext($route, $params, $pageField),
                'last' => self::setLast($route, $params, $meta, $pageField)
            ];
        } else {
            return [
                'self' => self::setSelf($route, $params),
                'first' => self::setFirst($route, $params,  $pageField),
                'prev' => self::setPrev($route, $params, $pageField),
            ];
        }
    }

    private static function setSelf($route, $params)
    {
        return ['href' => Url::to($route, $params)];
    }

    private static function setFirst($route, $params, $pageField)
    {
        $params[ $pageField] = 1;
        return ['href' => Url::to($route, $params)];
    }

    private static function setPrev($route, $params, $pageField)
    {
        $params[ $pageField]--;
        return ['href' => Url::to($route, $params)];
    }

    private static function setNext($route, $params, $pageField)
    {
        $params[$pageField]++;
        return ['href' => Url::to($route, $params)];
    }

    private static function setLast($route, $params, $meta, $pageField)
    {
        $params[$pageField] = $meta['pageCount'];
        return ['href' => Url::to($route, $params)];
    }
}