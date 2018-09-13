<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/4
 * Time: 17:45
 */

namespace App\Controller;


use App\Components\http\Formatter;
use App\Components\Log;
use Breeze\Http\Controller;
use Breeze\Http\Request;

class BaseController extends Controller
{
    public $request;

    /**
     * BaseController constructor.
     * @param $request
     */
    public function __construct(Request $request)
    {
        // 兼容非标准json post数据
        if (isset($request->server()->HTTP_CONTENT_TYPE) && $request->server()->HTTP_CONTENT_TYPE != 'application/json') {
            $post = json_decode($request->rawData()->scalar, true);
            $request->setPost($post);
            $request->setRequest($post);
        }

        $this->request = $request;
        $header = $this->request->post()->header ?? 'header';
        Formatter::setHeader($header);

        // 记录日志
        Log::info($this->request);
    }


}