<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/3
 * Time: 11:01
 */

namespace App\Components\pay;

use App\Components\Log;
use App\Exceptions\ErrorCode;
use Breeze\Config;
use Breeze\Helpers\Url;

class DokyPay extends BasePay
{
    private $url = 'https://gateway.dokypay.com/clientapi/unifiedorder';
    private $queryUrl = 'https://openapi.dokypay.com/trade/query';
    private $version = '1.0';
    private $prodName = 'southeast.asia';
    private $country = 'CN';

    private $merchant_id;
    private $merchant_key;

    public static $errorLog = APP_ROOT . 'storage/logs/dokypay-error.log';
    public static $notifyLog = APP_ROOT . 'storage/logs/dokypay-notify.log';

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
        $this->setMerchantId($config['MERCHANT_ID']);
        $this->setMerchantKey($config['MERCHANT_KEY']);
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

        $goods['sign'] = $this->setSign($goods, $this->app_key);
        ksort($goods);

        $logFile = APP_ROOT . 'storage/logs/dokypay.log';
        Log::write($logFile, json_encode($goods) . PHP_EOL);

        $data = $this->post($this->url, $goods);

        if (!empty($data) && isset($data['data']) && $data['data']['resultCode'] == '0000') {
            return $data['data']['url'];
        }

        throw new \Exception("统一下单接口调用失败");
    }

    /**
     * 查询订单
     * @param $merTransNo
     * @return array
     */
    public function queryOrder($merTransNo)
    {
        $data = [
            //'version' => $this->version,
            'merchantId' => $this->merchant_id,
            'tradeNo' => $merTransNo
        ];
        $data['sign'] = $this->setSign($data, $this->merchant_key);

        ksort($data);
        print_r($data);
        $response = $this->post($this->queryUrl, $data);
        if ($response == false) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_ORDER_DOES_NOT_EXIST];
        }

        $responseData = $response['data'];

        if (isset($responseData['resultCode']) && $responseData['resultCode'] == '3004') {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_ORDER_DOES_NOT_EXIST];
        }

        return $responseData;
    }

    private function checkIsFinish($data)
    {
        if (empty($data) || !isset($data['tradeStatus'])) {
            return false;
        }

        $tradeStatus = $data['tradeStatus'];
        if ($tradeStatus == 'success') {
            return ['status' => true, 'msg' => $tradeStatus];
        }

        return ['status' => false, 'msg' => $tradeStatus];
    }

    private function post($url, $data)
    {
        $client = $this->getHttpClient();
        $this->setGuzzleOptions([
            'headers' => [
                    'Content-Type: Application/json;charset=utf-8',
                    'Accept: text/html'
            ]
        ]);

        try {
            $result = $client->request('POST', $url,['json' => $data])
                            ->getBody()
                            ->getContents();

            return json_decode($result, true);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            echo $e->getMessage();
            return false;
        } catch (\Exception $e) {
            echo $e->getMessage();
            return false;
        }
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
     * @param $secret
     * @return string
     */
    public function setSign($data, $secret)
    {
        ksort($data);
        $str = '';
        foreach ($data as $key => $val) {
            $str .= ($key . '=' . $val . '&');
        }
        $str .= ('key=' . $secret);

        return bin2hex(hash("sha256", $str, true));
    }

    /**
     * 设置商户密钥
     * @param $merchant_id
     */
    public function setMerchantId($merchant_id)
    {
        $this->merchant_id = $merchant_id;
    }

    /**
     * 设置商户密钥
     * @param $key
     */
    public function setMerchantKey($key)
    {
        $this->merchant_key = $key;
    }

    /**
     * 校验签名
     * @param $data
     * @param $sign
     * @return bool
     */
    public function checkSign($data, $sign)
    {
        $encrypt = $this->setSign($data, $this->app_key);

        if ($encrypt != $sign) {
            return false;
        }

        return true;
    }
}