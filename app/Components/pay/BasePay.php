<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/14
 * Time: 10:43
 */

namespace App\Components\pay;


use GuzzleHttp\Client;

class BasePay extends Pay
{
    public function setAppId($appID)
    {
        $this->app_id = $appID;
    }

    public function setAppSecret($appSecret)
    {
        $this->app_key = $appSecret;
    }

    public function setProduct($productName)
    {
       $this->product = $productName;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    public function setShipping($shipping)
    {
        $this->shipping = $shipping;
    }

    public function setCurrency($currency)
    {
       $this->currency = $currency;
    }

    public function setDescription($description)
    {
       $this->description = $description;
    }

    public function setMerTransNo($orderNum)
    {
       $this->merTransNo = $orderNum;
    }

    public function setNotifyUrl($notifyUrl)
    {
       $this->notifyUrl = $notifyUrl;
    }

    public function setReturnUrl($returnUrl)
    {
        $this->returnUrl = $returnUrl;
    }

    public function setCancelUrl($cancelUrl)
    {
        $this->cancelUrl = $cancelUrl;
    }

    /**
     * @param mixed $options
     */
    public function setGuzzleOptions($options)
    {
        $this->guzzleOptions = $options;
    }

    /**
     * @return Client
     */
    public function getHttpClient()
    {
        return new Client($this->guzzleOptions);
    }


    public function unifiedOrder()
    {
        // TODO: Implement unifiedOrder() method.
    }

    public function init()
    {
        // TODO: Implement init() method.
    }

    public function queryOrder($orderNum)
    {
        // TODO: Implement init() method.
    }


}