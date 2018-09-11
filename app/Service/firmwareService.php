<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/6/28
 * Time: 17:13
 */

namespace App\Service;

use App\Components\Aliyun\MyOSS;
use App\Components\helper\ArrayHelper;
use App\Exceptions\ErrorCode;
use Illuminate\Database\Capsule\Manager as Capsule;

class firmwareService extends common
{
    /**
     * 绑定订单号的 dvb固件升级
     * @param string $type
     * @return array
     *
     */
    public function getFirmware($type = "dvb")
    {
        try {
            $orderId = $this->post('order_id', null, ['string']);
            $clientVersion = $this->post('version', 0);
        } catch (\InvalidArgumentException $e) {
            return ['status' => false, 'code' => $e->getCode()];
        }

        $firmwareIndex = Capsule::table('dvb_order as a')
                                 ->select('b.ID as fid')
                                 ->leftJoin('firmware_class AS b','a.id','=', 'b.order_id')
                                 ->where("order_num", "=",$orderId)
                                 ->first();

        if (is_null($firmwareIndex)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        $firmwareDetail = Capsule::table('firmware_detail')
                                   ->select(['url','content','force_update','ver','md5'])
                                   ->where("firmware_id", "=", $firmwareIndex->fid)
                                   ->first();

        if (is_null($firmwareDetail)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        $firmwareDetail = ArrayHelper::toArray($firmwareDetail);
        $serverVersion = $firmwareDetail['ver'];

        if ($type == 'dvb') {
            $isNeedUpdate = $this->judgeDvbUpdate($clientVersion,$serverVersion);
        } else {
            $isNeedUpdate = $this->judgeIsNeedUpdate($clientVersion,$serverVersion);
        }

        if ($isNeedUpdate == false) {
             return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_NEED_UPDATE];
        }

        if (empty($firmwareDetail['url'])) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        $firmwareDetail['url'] = (new MyOSS())->getSignUrl($firmwareDetail['url'],600);

        return ['status' => true, 'data' => $firmwareDetail];
    }



    /**
     * dvb固件升级判断 以.号分隔主次版本
     * @param $client
     * @param $server
     * @return bool
     */
    private function judgeDvbUpdate($client,$server)
    {
        if (is_null($client)) {
            return true;
        }

        $clientVersion = explode('_',$client);
        $serverVersion = explode('.',$server);

        //比较主次版本
        $clientPrimary = (int)$clientVersion[0];
        $clientSecondary = (int)$clientVersion[1];
        $serverPrimary = (int)$serverVersion[0];
        $serverSecondary = (int)$serverVersion[1];

        if ($clientPrimary < $serverPrimary) {
            return true;
        } else if ($clientPrimary == $serverPrimary && $clientSecondary < $serverSecondary) {
            return true;
        } else {
            return false;
        }

    }
}