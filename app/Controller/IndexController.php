<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/8/31
 * Time: 17:38
 */

namespace App\Controller;


use Breeze\Http\Controller as BaseController;

class IndexController extends BaseController
{
    public function index()
    {
       return 'hello world';
    }
}