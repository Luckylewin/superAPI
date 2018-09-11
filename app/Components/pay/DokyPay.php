<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/3
 * Time: 11:01
 */

namespace App\Components\pay;


use Breeze\Config;

class DokyPay
{
    private $url = 'https://gateway.dokypay.com/clientapi/unifiedorder';
    private $app_id;
    private $app_key;


    private $version = '1.0';
    private $prodName = 'southeast.asia';
    private $country = 'CN';
    private $currency = 'USD';
    private $amount;
    private $description;
    private $merTransNo;
    private $notifyUrl;
    private $returnUrl;


    public function __construct()
    {
        $config = Config::get('params.DOKYPAY');
        $this->app_id = $config['APP_ID'];
        $this->app_key = $config['APP_KEY'];

        if (empty($this->app_id) || empty($this->app_key)) {
            throw new \Exception('缺少dokypay的支付配置，请在配置文件params-local.php填写配置信息');
        }
    }

    /**
     * 统一下单
     * @return string
     * @throws \Exception
     */
    public function unifiedOrder()
    {
        $goods['amount'] = $this->amount;
        $goods['appId'] = $this->app_id;
        $goods['country'] = $this->country;
        $goods['currency'] = $this->currency;
        $goods['description'] = $this->description;
        $goods['merTransNo'] = $this->merTransNo;
        $goods['notifyUrl'] = $this->notifyUrl;
        $goods['prodName'] = $this->prodName;
        $goods['returnUrl'] = $this->returnUrl;
        $goods['version'] = $this->version;

        foreach ($goods as $field => $value) {
            if (empty($value)) {
                throw new \Exception($field . '必须设置');
            }
        }

        $goods['sign'] = $this->setSign($goods);
        ksort($goods);

        file_put_contents('/tmp/pay.log', json_encode($goods) . PHP_EOL, FILE_APPEND);

        $data = $this->post($this->url, $goods);

        if (!empty($data) && isset($data['data']) && $data['data']['resultCode'] == '0000') {
            return $data['data']['url'];
        }

        throw new \Exception("统一下单接口调用失败");

    }

    private function post($url, $data)
    {
        $ch = curl_init();
        $timeout = 5;

        $header = array(
            'Accept-Language: zh-cn',
            'Connection: Keep-Alive',
            'Cache-Control: no-cache',
            'Content-Type: Application/json;charset=utf-8',
            "X-Requested-With: XMLHttpRequest"
        );

        $json = json_encode($data);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $res = curl_exec($ch);

        return json_decode($res, true);

    }

    /**
     * 设置国家
     * @param $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * 设置货币
     * @param $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * 设置订单描述
     * @param $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * 设置商户订单号 自行检测是否有重复
     * @param $transNo
     */
    public function setMerTransNo($transNo)
    {
        $this->merTransNo = $transNo;
    }

    /**
     * 异步回调地址
     * @param $url
     */
    public function setNotifyUrl($url)
    {
        $this->notifyUrl = $url;
    }

    /**
     * 同步返回地址
     * @param $url
     */
    public function setReturnUrl($url)
    {
        $this->returnUrl = $url;
    }

    /**
     * @param $name string 产品名称
     */
    public function setProdName($name)
    {
        $this->prodName = $name;
    }

    /**
     * @param $version string API 版本
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @param $amount string 订单金额
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * 生成签名
     * @param $data
     * @return string
     */
    public function setSign($data)
    {
        ksort($data);
        $str = '';
        foreach ($data as $key => $val) {
            $str .= ($key . '=' . $val . '&');
        }
        $str .= ('key=' . $this->app_key);

        return bin2hex(hash("sha256", $str, true));
    }

    /**
     * 校验签名
     * @param $data
     * @param $sign
     * @return bool
     */
    public function checkSign($data, $sign)
    {
        $encrypt = $this->setSign($data);

        if ($encrypt != $sign) {
            return false;
        }

        return true;
    }
}