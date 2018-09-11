<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/8/31
 * Time: 15:02
 */

namespace Breeze\Http;

/**
 * 中间件接口
 * Interface MiddleWareInterface
 * @package Breeze\Http
 */
interface MiddlewareInterface
{
    public function handle(Request $request);
}