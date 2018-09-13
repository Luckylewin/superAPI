<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/4
 * Time: 16:55
 */

namespace App\Controller;


use App\Components\helper\ArrayHelper;
use App\Components\http\Formatter;
use App\Service\payService;
use Breeze\Http\Controller;
use Breeze\Http\Request;
use Breeze\Http\Response;

class PayController extends Controller
{
    /**
     * dokypay 异步通知入口
     * @param Request $request
     * @return array|string
     */
    public function notifyByPost(Request $request)
    {
        Formatter::format(Formatter::JSON);
        $data = $request->post();
        $data = ArrayHelper::toArray($data);
        return (new payService($request))->dokypayNotify($data);
    }

    /**
     * dokypay 同步通知入口
     * @param Request $request
     * @return array|string
     * @throws \Exception
     */
    public function notifyByGet(Request $request)
    {
        Formatter::format(Formatter::TEXT);
        $data = $request->get();
        $data = ArrayHelper::toArray($data);
        return (new payService($request))->dokypayNotify($data, false);
    }

    /**
     * paypal 支付同步通知入口
     * @param Request $request
     * @return string
     */
    public function paypalCallback(Request $request)
    {
        Response::format(Response::TEXT);
        $data = $request->get();
        $data = ArrayHelper::toArray($data);

        return (new payService($request))->paypalNotify($data, false);
    }
}