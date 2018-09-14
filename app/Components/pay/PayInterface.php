<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/14
 * Time: 10:12
 */

namespace App\Components\pay;


interface PayInterface
{
    public function setAppId($appID);

    public function setAppSecret($appSecret);

    public function setProduct($productName);

    public function setAmount($amount);

    public function setShipping($shipping);

    public function setQuantity($quantity);

    public function setCurrency($currency);

    public function setDescription($description);

    public function setMerTransNo($orderNum);

    public function setNotifyUrl($notifyUrl);

    public function setReturnUrl($returnUrl);

    public function setCancelUrl($cancelUrl);

    /**
     * 统一下单
     * @return string
     */
    public function unifiedOrder();

    /**
     * 初始化支付配置
     * @return mixed
     */
    public function init();
}