<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/7/9
 * Time: 16:42
 */

namespace App\Service;

use App\Components\pay\DokyPay;
use App\Components\helper\ArrayHelper;
use App\Components\pay\Paypal;
use App\Exceptions\ErrorCode;
use Endroid\QrCode\QrCode;
use Illuminate\Database\Capsule\Manager as Capsule;

class payService extends common
{

    public function pay()
    {
        try {
            $order_sign = $this->post('order_sign');
            $payType = $this->post('paytype', 'dokypay', ['in', ['dokypay', 'paypal']]);

        } catch (\InvalidArgumentException $e) {
            return ['status' => false, 'code' => $e->getCode()];
        }

        $order = Capsule::table('iptv_order')
                            ->where('order_sign', '=', $order_sign)
                            ->first();

        if (is_null($order)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_PARAMETER];
        }

        if (isset($order->order_status) && $order->order_status) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_PAYMENT_REPEATED];
        }

        $amount = $order->order_money;
        $description = $order->order_info;
        $productName = $order->order_info;

        if ($payType == 'dokypay') {
            $url = $this->dokypay($order_sign, $amount, $description);
        } else {
            $url = $this->paypal($order_sign, $amount, $productName, $description);
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
     * @return string
     */
    public function paypal($order_sign, $amount, $productName, $description)
    {
        return Paypal::pay($order_sign, $amount, $productName, $description);
    }

    /**
     * dokypay
     * @param $order_sign
     * @param $amount
     * @param $description
     * @return string
     */
    public function dokypay($order_sign, $amount, $description)
    {
        try {
            $dokyPay = new DokyPay();
            $dokyPay->setNotifyUrl('http://' .$_SERVER['HTTP_HOST'] . '/notify/dokypay');
            $dokyPay->setReturnUrl('http://' . $_SERVER['HTTP_HOST'] . '/return/dokypay');
            $dokyPay->setMerTransNo($order_sign);
            $dokyPay->setAmount($amount);
            $dokyPay->setDescription($description);
            $url = $dokyPay->unifiedOrder();
            return $url;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function dokypayNotify($data, $async = true)
    {
        if (isset($data['transStatus'])) {
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
                    $order = Capsule::table('iptv_order')
                                    ->where('order_sign' , '=', $order_num)
                                    ->first();

                    if (is_null($order)) {
                        return $this->sendResult(5, $async);
                    }

                    $order = ArrayHelper::toArray($order);

                    if ($order['order_status'] == '0') {
                        Capsule::table('iptv_order')
                                    ->where('order_sign', '=', $order_num)
                                    ->update([
                                            'order_ispay' => 1,
                                            'order_status' => 1,
                                            'order_paytime' => time(),
                                            'order_confirmtime' => time()
                                    ]);


                        $this->callBack($order, $order_num);

                        return $this->sendResult(0, $async);
                    } else {
                        return $this->sendResult(1, $async);
                    }

                } else {
                    return $this->sendResult(3, $async);

                }
            }

        } else {

            return $this->sendResult(4, $async);
        }
    }

    public function sendResult($code, $async)
    {
        switch ($code) {
            case '0':
                echo "支付回调处理成功";
                if ($async) {
                    return ['result' => 'pay success'];
                } else {
                    return $this->callbackPage('success', 'Payment Successful', 'please restart APP');
                }

            case '1':
                echo "该订单已经被处理";
                if ($async) {
                    return ['result' => 'had deal this order ,success'];
                } else {
                    return $this->callbackPage('warn', 'had deal this order');
                }

            case '3':
                echo "签名校验失败";
                if ($async) {
                    return ['result' => 'invalid sign'];
                } else {
                    return $this->callbackPage('warn', 'Invalid Sign');
                }

            case '4':
                echo "错误的回调";
                if ($async) {
                    return ['result' => 'invalid callback'];
                } else {
                    return $this->callbackPage('warn', 'Invalid Callback');
                }
            default:
                if ($async) {
                    return ['result' => 'An error occured'];
                } else {
                    return $this->callbackPage('warn', 'Invalid Callback');
                }
        }
    }

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
        $order_sign = $this->post('order_sign');

        $order = Capsule::table('iptv_order')
                         ->where('order_sign', '=', $order_sign)
                         ->first();

        if (is_null($order)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        return ['status' => true, 'data' => ArrayHelper::toArray($order)];
    }

    // 业务处理
    public function callBack($order, $order_num)
    {
        Capsule::table('ott_order')
                    ->where('order_num', '=', $order_num)
                    ->update(['is_valid' => '1']);

        if ($order['order_type'] == 'ott') {
            $ott_order = Capsule::table('ott_order')
                              ->where('order_num' , '=', $order['order_sign'])
                              ->first();

            if (!is_null($ott_order)) {
                $user = Capsule::table('yii2_user')->where('username', '=', $order['order_uid'])->first();

                if ($user) {
                    $baseTime =  $user->vip_expire_time > time() ? $user->vip_expire_time : time();
                    $expire_time = $baseTime + $ott_order->expire_time;

                    //更新用户的过期时间
                    Capsule::table('yii2_user')
                            ->where('username', '=', $order['order_uid'])
                            ->update([
                                'identity_type' => '1',
                                'is_vip' => 1,
                                'vip_expire_time' => $expire_time,
                                'updated_at' => time()]
                            );

                    echo "已更新用户的信息";
                }
            }
        }
    }


}