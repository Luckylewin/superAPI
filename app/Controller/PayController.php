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
use Breeze\Http\Request;
use Breeze\Http\Response;

class PayController extends BaseController
{

    // 支付接口
    public function pay()
    {
        try {
            $order_sign = $this->post('order_sign');
            $payType    = $this->post('pay_type', 'dokypay');
        } catch (\Exception $e) {
            return $this->fail($e->getCode());
        }
        
        $data = (new payService($this->request))->pay($order_sign, $payType);

        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    /**
     * dokypay 异步通知入口
     * @param Request $request
     * @return array|string
     * @throws
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
        return (new payService($request))->paypalNotify($request, false);
    }

    public function getOrderStatus(): string
    {
        $order_sign = $this->request->post('order_sign');
        $data = (new payService($this->request))->getOrderInfo($order_sign);
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }
}