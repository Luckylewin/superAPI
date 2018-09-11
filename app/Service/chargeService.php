<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/7/13
 * Time: 18:37
 */

namespace App\Service;

use App\Components\cache\Redis;
use App\Components\helper\ArrayHelper;
use App\Exceptions\ErrorCode;
use Illuminate\Database\Capsule\Manager as Capsule;

class chargeService extends common
{

    /**
     * 获取价目表
     * @return mixed
     */
    public function getOttPriceList()
    {
       $lang = $this->post('lang', 'en_US');

       $data = Capsule::table('ott_price_list')
                        ->where('type', '=', 'ott')
                        ->orderBy('value','asc')
                        ->get();

       if ($data->count() == false) {
           return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
       }

       $data = ArrayHelper::toArray($data);

       $text['en_US'] = ['1' => '1 month', '3' => '3 month', '6' => '6 month', '12' => '1 year'];
       $text['zh_CN'] = ['1' => '1个月', '3' => '3个月', '6' => '6个月', '12' => '1年'];

       foreach ($data as $key => $val) {
           if ($lang == 'en_US') {
                $data[$key]['text'] = $text['en_US'][$val['value']];
           } else {
               $data[$key]['text'] = $text['zh_CN'][$val['value']];
           }
           $data[$key]['type'] = $val['value'];
           unset($data[$key]['value'], $data[$key]['id']);
       }

       $response['currency'] = 'USD';
       $response['items'] = $data;

       return ['status' => true, 'data' => $response];
    }

    // 生成开通分类观看服务订单
    public function openService()
    {
        try {
            // 查找分类价格
            $genre = $this->post('genre');
            $type = $this->post('type');
            $timestamp = $this->post('timestamp');
            $sign = $this->post('sign');
        } catch (\InvalidArgumentException $e) {
            return ['status' => false, 'code' => $e->getCode()];
        }

        $serverSign = md5($timestamp . 'topthinker');

        if (!in_array($type, [1, 3, 6, 12])) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_PARAMETER];
        }

        if ($sign != $serverSign) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_SIGNATURE];
        }

        $genre = Capsule::table('ott_main_class')
                        ->where([
                            ['use_flag', '=', 1],
                            ['list_name', '=', $genre]
                        ])
                        ->select(['name', 'list_name', 'is_charge', 'one_month_price', 'three_month_price', 'six_month_price', 'one_year_price'])
                        ->first();

        if (is_null($genre)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_PARAMETER];
        }

        $genre = ArrayHelper::toArray($genre);

        if ($genre['is_charge'] == false) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_NEED_TO_PAY];
        }

        $goods = $this->_getGoodsInfo($genre, $type);

        $order_sign = $this->_generateOrder();  //产生一个订单号

        // 订单流水表
        $order['order_sign'] = $order_sign;
        $order['order_money'] = $goods['price'];
        $order['order_uid'] = $this->uid;
        $order['order_total'] = 1;
        $order['order_info'] = "直播分类收费";
        $order['order_paytype'] = '';
        $order['order_type'] = 'ott';
        $order['order_addtime'] = time();

        // 直播分类订单表
        $ott['order_num'] = $order_sign;
        $ott['uid'] = $this->uid;
        $ott['genre'] = $genre['list_name'];
        $ott['expire_time'] = $goods['time'];

        Capsule::beginTransaction();
        try {
            Capsule::table('ott_order')->insert($ott);
            Capsule::table('iptv_order')->insert($order);
            Capsule::commit();
            // 返回订单详情
            return [
                'status' => true,
                'data' => [
                    'order_sign' => $order['order_sign'],
                    'order_money' => $order['order_money'],
                    'order_uid' => $this->uid,
                    'order_info' => $order['order_info'],
                    'expire' => time() + 2700
                ]
            ];

        } catch (\Exception $e) {
            Capsule::rollBack();
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_OPERATE_DATABASE_ERROR];
        }
    }

    // 查询
    private function _getGoodsInfo($genre, $type)
    {
        switch ($type)
        {
            case '1':
                $price = $genre['one_month_price'];
                $time = 86400 * 31;
                break;
            case '3':
                $price = $genre['three_month_price'];
                $time = 86400 * 31 * 3;
                break;
            case '6':
                $price = $genre['six_month_price'];
                $time = 86400 * 31 * 6;
                break;
            case '12':
                $price = $genre['one_year_price'];
                $time = 86400 * 31 * 12;
                break;
            default:
                $price = $genre['one_month_price'];
                $time = 86400 * 31;
                break;
        }

        return ['price' => $price, 'time' => $time];
    }

    /**
     * 产生订单号
     * @return string
     */
    private function _generateOrder()
    {
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        return $orderSn = $yCode[intval(date('Y')) - 2018] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
    }

    /**
     * 续费卡
     * @return array
     */
    public function renew()
    {
        $cardSecret = $this->post('card_secret', null, ['string']);

        $card = Capsule::table('sys_renewal_card')
                         ->select(['card_num','is_valid','is_del','card_contracttime'])
                         ->where('card_secret',"=", $cardSecret)
                         ->first();

        if (is_null($card) || !$card->is_valid || $card->is_del) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_INVALID_CARD];
        }

        //查询用户状态
        $macInfo = Capsule::table('mac')
                            ->select(['duetime','use_flag'])
                            ->where("MAC","=", $this->uid)
                            ->first();

        if (is_null($macInfo)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_UID_NOT_EXIST];
        }

        $contractTime = $card->card_contracttime;
        $useTime = time();

        if ($macInfo->use_flag == 1 && $macInfo->duetime != '0000-00-00 00:00:00') {
            $duetime = date('Y-m-d H:i:s',strtotime(substr($macInfo->duetime,0,10) . " + {$contractTime}"));
        } else {
            $duetime = date('Y-m-d H:i:s',strtotime("+ {$contractTime}"));
        }

        //开启事务

        Capsule::table('mac')
                        ->where("MAC", '=', $this->uid)
                        ->update([
                            'use_flag' => 1,
                            'duetime' => $duetime,
                            'contract_time' => $contractTime,
                        ]);

        Capsule::table('sys_renewal_card')
                           ->where('card_num', '=', $card->card_num)
                           ->update(['is_valid' => 0]);

        Capsule::table('iptv_renew')->insert(
                   [
                        'mac' => $this->uid,
                        'card_num' => $card->card_num,
                        'date' => $useTime,
                        'renew_period' => $contractTime
                    ]
        );

        //判断
        $this->getRedis(Redis::$REDIS_DEVICE_STATUS)->hSet($this->uid,"use_flag",0);

        return [
            'status' => true,
            'data' => [
                'mac' => $this->uid,
                'duetime' => $duetime
            ]
        ];

    }

}