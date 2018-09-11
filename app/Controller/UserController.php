<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/11
 * Time: 11:12
 */

namespace App\Controller;


use Breeze\Http\Response;

class UserController extends BaseController
{
    public function getIP()
    {
        Response::format(Response::TEXT);
        return $this->request->ip();
    }
}