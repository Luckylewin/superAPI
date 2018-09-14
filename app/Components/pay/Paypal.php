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

class Paypal extends BasePay
{
    public function __construct()
    {
       $this->init();
    }

    public function init()
    {
        $config  = Config::get('params.PAYPAL');
        $this->setAppId($config['CLIENT_ID']);
        $this->setAppSecret($config['SECRET']);
        $this->setReturnUrl(Url::to('paypalCallback',['success'=>'true']));
        $this->setCancelUrl(Url::to('paypalCallback',['success'=>'false']));

        $logFile = LOG_PATH . 'paypal.log';
        FileHelper::createFile($logFile);

        $apiContext = new ApiContext(new OAuthTokenCredential($this->app_id, $this->app_key));
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
     * @return bool|string
     * @throws \Exception
     */
    public function unifiedOrder()
    {
        $requires = ['merTransNo','amount', 'product', 'description'];
        foreach ($requires as $field) {
            if (empty($this->$field)) {
                throw new \Exception("$field 参数未设置");
            }
        }

        //创建paypal对象
        $apiContext = $this->init();

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $item = new Item();
        $item->setName($this->product)
                ->setCurrency($this->currency)
                ->setQuantity($this->quantity)
                ->setPrice($this->amount);

        $itemList = new ItemList();
        $itemList->setItems([$item]);

        $details = new Details();
        $details->setShipping($this->shipping)
                ->setSubtotal($this->amount);

        $amount = new Amount();
        $amount->setCurrency($this->currency)
                ->setTotal($this->amount)
                ->setDetails($details);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
                    ->setItemList($itemList)
                    ->setDescription($this->description)
                    ->setInvoiceNumber($this->merTransNo);

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($this->returnUrl)   //设置支付成功回调地址
                     ->setCancelUrl($this->cancelUrl); //设置支付失败回调地址

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
                print_r($data);
                if (isset($data['name'])) {
                    $err .= isset($data['name']) ? $data['name'] : '' . " - " . $data['message'] . "<br>";
                    if (isset($data['details'])){
                        $err .= "<ul>";
                        foreach ($data['details'] as $details){
                            $err .= "<li>". $details['field'] . ": " . $details['issue'] . "</li>";
                        }
                        $err .= "</ul>";
                    }
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
    public function notifyCheck(Request $request)
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

        $apiContext = $this->init();
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

            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_ORDER_HAS_BEEN_PROCESSED];
        }
    }
}