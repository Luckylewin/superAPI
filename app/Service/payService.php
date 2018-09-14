<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/7/9
 * Time: 16:42
 */
namespace App\Service;

use App\Components\Log;
use App\Components\pay\DokyPay;
use App\Components\helper\ArrayHelper;
use App\Components\pay\Paypal;
use App\Exceptions\ErrorCode;
use Breeze\Http\Request;
use Endroid\QrCode\QrCode;
use Illuminate\Database\Capsule\Manager as Capsule;

class payService extends common
{
    public function pay()
    {
        try {
            $order_sign = $this->post('order_sign');
            $payType = $this->post('pay_type', 'dokypay', ['in', ['dokypay', 'paypal']]);
        } catch (\Exception $e) {
            return ['status' => false, 'code' => $e->getCode()];
        }

        $order = Capsule::table('iptv_order')
                            ->where('order_sign', '=', $order_sign)
                            ->first();

        if (is_null($order)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_PARAMETER];
        }
        // 判断订单是否超时
        if (time() - $order->order_addtime >= 1800) {
             return ['status' => false, 'code' => ErrorCode::$RES_ERROR_OVERTIME_PAYMENT];
        }

        if (isset($order->order_status) && $order->order_status) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_PAYMENT_REPEATED];
        }

        $amount = $order->order_money;
        $description = $order->order_info;
        $productName = $order->order_info;

        try {
            if ($payType == 'dokypay') {
                $url = $this->dokypay($order_sign, $amount, $description);
            } else {
                $url = $this->paypal($order_sign, $amount, $productName, $description);
            }

        } catch (\Exception $e) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_PAYMENT_GO_WRONG];
        }

        if ($url == false) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_PAYMENT_GO_WRONG];
        }

        $qrCode = new QrCode();
        $qrCode->setText($url);

        $response['qrcode'] = base64_encode($qrCode->writeString());
        $response['url'] = $url;
        $response['type'] = $payType;

        return ['status' => true, 'data' => $response];
    }

    /**
     * @param $order_sign
     * @param $amount
     * @param $productName
     * @param $description
     * @return bool|string
     * @throws \Exception
     */
    public function paypal($order_sign, $amount, $productName, $description)
    {
        $paypal = new Paypal();
        $paypal->setMerTransNo($order_sign);
        $paypal->setAmount($amount);
        $paypal->setProduct($productName);
        $paypal->setDescription($description);

        return $paypal->unifiedOrder();
    }

    /**
     * dokypay
     * @param $order_sign
     * @param $amount
     * @param $description
     * @return string
     * @throws \Exception
     */
    public function dokypay($order_sign, $amount, $description)
    {
        $dokyPay = new DokyPay();
        $dokyPay->setMerTransNo($order_sign);
        $dokyPay->setAmount($amount);
        $dokyPay->setDescription($description);
        $url = $dokyPay->unifiedOrder();
        return $url;
    }

    /**
     * 处理paypal回调
     * @param $request
     * @param bool $async
     * @return array|string
     */
    public function paypalNotify(Request $request, $async = true)
    {
        $this->stdout("paypal 同步通知",'INFO');

        $paypal = new Paypal();
        $result = $paypal->notifyCheck($request);

        if ($result['status'] == true) {

            Log::write(Paypal::$notifyLog, json_encode($request->get()) . PHP_EOL);

            $order_num = $result['order_num'];
            $order = $this->findOrderByOrdernum($order_num);

            if (is_null($order)) {
                Log::write(Paypal::$errorLog, "订单{$order_num}支付成功，但是找不到该订单" . PHP_EOL);
                return $this->sendResult(ErrorCode::$RES_ERROR_ORDER_DOES_NOT_EXIST, $async);
            }

            $order = ArrayHelper::toArray($order);

            if ($order['order_status'] == '0') {
                $this->updateOrder($order_num);
                $this->callBack($order, $order_num, 'paypal');

                return $this->sendResult(ErrorCode::$RES_SUCCESS_PAYMENT_SUCCESS, $async);
            } else {
                return $this->sendResult(ErrorCode::$RES_ERROR_ORDER_HAS_BEEN_PROCESSED, $async);
            }

        }

        return $this->sendResult($result['code'], $async);
    }

    /**
     * dokypay 支付异步处理
     * @param $data
     * @param bool $async
     * @return array|string
     * @throws \Exception
     */
    public function dokypayNotify($data, $async = true)
    {
        $this->stdout("dokypay " .  $async ? '异步' : '同步' ."通知",'INFO');

        if (isset($data['transStatus'])) {
            Log::write(DokyPay::$notifyLog, json_encode($data) . PHP_EOL);

            $status = $data['transStatus'];
            if ($status == 'success') {
                $sign = $data['sign'];

                unset($data['r'], $data['sign']);

                // 检测签名
                $DokyPay = new DokyPay();
                $valid = $DokyPay->checkSign($data, $sign);

                if ($valid) {
                    $order_num = $data['merTransNo'];
                    // 查询订单类别
                    $order = $this->findOrderByOrdernum($order_num);

                    if (is_null($order)) {
                        Log::write(DokyPay::$errorLog, "订单{$order_num}支付成功，但是找不到该订单" . PHP_EOL);
                        return $this->sendResult(ErrorCode::$RES_ERROR_ORDER_DOES_NOT_EXIST, $async);
                    }

                    $order = ArrayHelper::toArray($order);

                    if ($order['order_status'] == '0' || $order['order_ispay'] == 0) {
                        $this->updateOrder($order_num);
                        $this->callBack($order, $order_num, 'dokypay');

                        return $this->sendResult(ErrorCode::$RES_SUCCESS_PAYMENT_SUCCESS, $async);
                    } else {
                        return $this->sendResult(ErrorCode::$RES_ERROR_ORDER_HAS_BEEN_PROCESSED, $async);
                    }

                } else {
                    return $this->sendResult(ErrorCode::$RES_ERROR_INVALID_SIGN, $async);
                }
            } else {
                return $this->sendResult(ErrorCode::$RES_ERROR_PAYMENT_FAILED, $async);
            }

        } else {
            return $this->sendResult(ErrorCode::$RES_ERROR_INVALID_CALLBACK, $async);
        }
    }

    private function findOrderByOrdernum($order_num)
    {
        return Capsule::table('iptv_order')
            ->where('order_sign' , '=', $order_num)
            ->first();
    }

    private function updateOrder($order_num)
    {
        Capsule::table('iptv_order')
            ->where('order_sign', '=', $order_num)
            ->update([
                'order_ispay' => 1,
                'order_status' => 1,
                'order_paytime' => time(),
                'order_confirmtime' => time()
            ]);
    }

    public function sendResult($code, $async)
    {
        $msg = ErrorCode::getError($code);
        $status = $code == ErrorCode::$RES_SUCCESS_PAYMENT_SUCCESS ? 'success' : 'warn';

        if ($async) {
            return ['result' => $msg];
        } else {
            return $this->callbackPage($status, $msg);
        }
    }

    /**
     * 返回页面提示
     * @param $status
     * @param $title
     * @param string $detail
     * @return string
     */
    public function callbackPage($status,$title, $detail = 'please retry again')
    {

    $doc =<<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
            <title>Payment Status</title>
            <link rel="stylesheet" href="https://cdn.bootcss.com/weui/1.1.2/style/weui.min.css"/></head>
            <body>
            <div class="weui-msg">
                <div class="weui-icon_area"><i class="weui-icon-{$status} weui-icon_msg"></i></div>
                <div class="weui-text_area">
                <h2 class="weui-msg_title">{$title}</h2>
                <p class="weui-msg__desc">{$detail}</p>
            </div>
        </body>
        </html>
HTML;

        return $doc;
    }

    public function getOrderInfo()
    {
        try {
            $order_sign = $this->post('order_sign');
        } catch (\Exception $e) {
            return ['status' => false, 'code' => $e->getCode()];
        }

        $order = Capsule::table('iptv_order')
                         ->where('order_sign', '=', $order_sign)
                         ->first();

        if (is_null($order)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        return ['status' => true, 'data' => ArrayHelper::toArray($order)];
    }

    // 业务处理
    public function callBack($order, $order_num, $payType)
    {
        // 更新订单支付方式
        Capsule::table('iptv_order')
                        ->where('order_sign', '=', $order_num)
                        ->update(['order_paytype' => $payType]);

        Capsule::table('ott_order')
                    ->where('order_num', '=', $order_num)
                    ->update(['is_valid' => '1']);

        if ($order['order_type'] == 'ott') {
            if (CHARGE_MODE == 1) {
                $this->chargeWithMember($order['order_sign']);
            } else if(CHARGE_MODE == 2) {
                $this->chargeWithGenre($order['order_sign']);
            }
        }

        $this->stdout("支付回调业务逻辑处理成功", "SUCCESS");
    }

    /**
     * 按分类收费模式 业务处理
     * @param $order_sign
     * @return bool
     */
    public function chargeWithGenre($order_sign)
    {
        $ott_order = Capsule::table('ott_order')
                            ->where('order_num' , '=',$order_sign )
                            ->first();

        if (is_null($ott_order)) {
            return false;
        }

        $access = Capsule::table('ott_access')
                            ->where([
                                ['mac', '=', $ott_order->uid],
                                ['genre', '=', $ott_order->genre],
                            ])
                            ->first();

        if (!empty($access)) {
            $baseTime =  $access->expire_time > time() ? $access->expire_time : time();
            $expire_time = $baseTime + $ott_order->expire_time;

            //更新用户的过期时间
            Capsule::table('ott_access')
                ->where([
                    ['genre', '=', $ott_order->genre],
                    ['mac', '=', $ott_order->uid]
                ])
                ->update([
                    'is_valid' => 1,
                    'expire_time' => $expire_time,
                    'deny_msg' => 'normal usage'
                ]);
        } else {
            Capsule::table('ott_access')
                ->insert([
                    'is_valid' => 1,
                    'expire_time' => time() + $ott_order->expire_time,
                    'deny_msg' => 'normal usage'
                ]);
        }

        return true;
    }

    /**
     * 按会员收费模式 业务处理
     * @param $order_sign
     * @return bool
     */
    public function chargeWithMember($order_sign)
    {
        $ott_order = Capsule::table('ott_order')
            ->where('order_num' , '=',$order_sign )
            ->first();

        if (is_null($ott_order)) {
            return false;
        }

        $user = Capsule::table('yii2_user')
            ->where('username', '=', $ott_order->uid)
            ->first();

        if ($user) {
            $baseTime =  $user->vip_expire_time > time() ? $user->vip_expire_time : time();
            $expire_time = $baseTime + $ott_order->expire_time;

            //更新用户的过期时间
            Capsule::table('yii2_user')
                ->where('username', '=', $ott_order->uid)
                ->update([
                        'identity_type' => '1',
                        'is_vip' => 1,
                        'vip_expire_time' => $expire_time,
                        'updated_at' => time()]
                );

            $this->stdout("已更新用户的信息", "ERROR");
        }

        return true;
    }

}