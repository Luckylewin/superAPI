<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/8/31
 * Time: 17:39
 */

namespace App\Middleware;


use Breeze\Http\MiddlewareInterface;
use Breeze\Http\Request;

class AppMiddleware implements MiddlewareInterface
{
    // TODO: Implement handle() method.
    public function handle(Request $request)
    {
        return $request;
    }

}