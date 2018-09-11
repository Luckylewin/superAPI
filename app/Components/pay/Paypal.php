<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/10
 * Time: 11:36
 */

namespace App\Components\pay;

use App\Components\helper\FileHelper;
use App\Components\helper\Func;
use App\Exceptions\ErrorCode;
use Breeze\Config;
use Breeze\Helpers\Url;
use Breeze\Http\Request;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;

class Paypal
{
    public static function init()
    {
        $config  = Config::get('params.PAYPAL');
        $clientId = $config['CLIENT_ID'];
        $clientSecret = $config['SECRET'];
        $logFile = LOG_PATH . 'paypal.log';

        FileHelper::createFile($logFile);

        $apiContext = new ApiContext(new OAuthTokenCredential($clientId, $clientSecret));
        $apiContext->setConfig([
            'mode' => 'live',
            'log.LogEnabled' => true,
            'log.FileName' => $logFile,
            'log.LogLevel' => 'DEBUG', // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
            'cache.enabled' => true,
        ]);

        return $apiContext;
    }

    /**
     * 下单
     * @param $order_sign string 订单号
     * @param $amount string 数量
     * @param $productName string 产品名称
     * @param $order_description string 产品描述
     * @return string
     */
    public static function pay($order_sign, $amount, $productName, $order_description)
    {
        $product = $productName;
        $price = $amount;
        $shipping = 0.00; //运费
        $invoice_number = $order_sign; //订单号
        $successCallback = Url::to('paypalCallback',['success'=>'true']);   //成功支付回调
        $cancelCallback = Url::to('paypalCallback',['success'=>'true']);  //取消回调
        $total = $price; //总金额
        $quantity = 1; //数量
        $currency = 'USD'; //货币
        $description = $order_description;  //订单描述信息

        //创建paypal对象
        $apiContext = paypal::init();

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $item = new Item();
        $item->setName($product)
                ->setCurrency($currency)
                ->setQuantity($quantity)
                ->setPrice($price);

        $itemList = new ItemList();
        $itemList->setItems([$item]);

        $details = new Details();
        $details->setShipping($shipping)
                ->setSubtotal($price);

        $amount = new Amount();
        $amount->setCurrency($currency)
                ->setTotal($total)
                ->setDetails($details);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
                    ->setItemList($itemList)
                    ->setDescription($description)
                    ->setInvoiceNumber($invoice_number);

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($successCallback)   //设置支付成功回调地址
                     ->setCancelUrl($cancelCallback); //设置支付失败回调地址

        $payment = new Payment();
        $payment->setIntent('sale')
                ->setPayer($payer)
                ->setRedirectUrls($redirectUrls)
                ->setTransactions([$transaction]);

        try {
            $payment->create($apiContext);
            return $payment->getApprovalLink();
        } catch (PayPalConnectionException $e) {
            $error = self::PaypalError($e);
            print_r($error);
           return false;
        }
    }

    /**
     * @param $e PayPalConnectionException
     * @return string
     */
    public static function  PaypalError($e)
    {
        $err = "";
        do {
            if (is_a($e, "PayPal\Exception\PayPalConnectionException")){
                $data = json_decode($e->getData(),true);
                $err .= $data['name'] . " - " . $data['message'] . "<br>";
                if (isset($data['details'])){
                    $err .= "<ul>";
                    foreach ($data['details'] as $details){
                        $err .= "<li>". $details['field'] . ": " . $details['issue'] . "</li>";
                    }
                    $err .= "</ul>";
                }
            }else{
                //some other type of error
                $err .= sprintf("%s:%d %s (%d) [%s]\n", $e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), get_class($e));
            }
        } while($e = $e->getPrevious());

        return $err;
    }

    /**
     * paypal 同步返回通知
     * @param Request $request
     * @return array
     */
    public static function notifyCheck(Request $request)
    {
        if ($request->get('success') == 'false') {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_INVALID_SIGN ];
        }

        if (!$request->get('success') ||
            !$request->get('paymentId') ||
            !$request->get('PayerID')
        ) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_INVALID_SIGN ];
        }

        $paymentID = $request->get('paymentId');
        $payerId = $request->get('PayerID');

        $apiContext = paypal::init();
        $payment = Payment::get($paymentID, $apiContext);

        $execute = new PaymentExecution();
        $execute->setPayerId($payerId);

        try{
            $result = $payment->execute($execute, $apiContext);
            $result = Func::object_array($result);
            $result = current(array_values($result));
            $result = current($result['transactions']);
            $result = array_values($result)[0];
            $invoice_number = $result['invoice_number'];

            return ['status' => true, 'order_num' => $invoice_number];
        } catch(\Exception $e){
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_INVALID_SIGN];
        }
    }
}