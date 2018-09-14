<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/14
 * Time: 10:29
 */

namespace App\Components\pay;


abstract class Pay implements PayInterface
{
    /**
     * @var string CLIENT_ID
     */
    protected $app_id;

    /**
     * @var string SECRET
     */
    protected $app_key;

    /**
     * @var string 订单对象
     */
    protected $product;

    /**
     * @var integer 总金额
     */
    protected $amount;

    /**
     * @var integer 数量
     */
    protected $quantity = 1;

    /**
     * @var string 运费
     */
    protected $shipping = '0.00';

    /**
     * @var string 货币
     */
    protected $currency = 'USD';

    /**
     * @var string 订单描述
     */
    protected $description;

    /**
     * @var string 商户自定义订单号
     */
    protected $merTransNo;

    /**
     * @var string 异步通知URL
     */
    protected $notifyUrl;

    /**
     * @var string 异步通知URL
     */
    protected $returnUrl;

    /**
     * @var string 取消支付返回URL
     */
    protected $cancelUrl;
}