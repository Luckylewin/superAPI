<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/3
 * Time: 11:01
 */

namespace App\Components\pay;

use App\Components\Log;
use Breeze\Config;
use Breeze\Helpers\Url;

class DokyPay extends BasePay
{
    private $url = 'https://gateway.dokypay.com/clientapi/unifiedorder';
    private $version = '1.0';
    private $prodName = 'southeast.asia';
    private $country = 'CN';

    public function __construct()
    {
        $this->init();
    }

    /**
     * @return mixed|void
     */
    public function init()
    {
        $config = Config::get('params.DOKYPAY');
        $this->setAppId($config['APP_ID']);
        $this->setAppSecret($config['APP_KEY']);
        $this->setNotifyUrl(Url::to('notify/dokypay'));
        $this->setReturnUrl(Url::to('return/dokypay'));
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

        $logFile = APP_ROOT . 'storage/logs/dokypay.log';
        Log::write($logFile, json_encode($goods) . PHP_EOL);

        $data = $this->post($this->url, $goods);

        if (!empty($data) && isset($data['data']) && $data['data']['resultCode'] == '0000') {
            return $data['data']['url'];
        }

        throw new \Exception("统一下单接口调用失败");

    }

    private function  post($url, $data)
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
     * @param $version string API 版本
     */
    public function setVersion($version)
    {
        $this->version = $version;
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