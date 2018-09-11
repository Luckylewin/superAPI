<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/6/28
 * Time: 18:08
 */

namespace App\Service;

use App\Components\cache\Redis;
use App\Components\encrypt\AES;
use App\Exceptions\ErrorCode;
use Illuminate\Database\Capsule\Manager as Capsule;

class userService extends common
{
    /**
     * 获取MAC地址过期时间
     * @return mixed
     */
    public function getMacExpireTime()
    {
        $uid = $this->uid;
        $redis = $this->getRedis(Redis::$REDIS_DEVICE_STATUS);
        $user = $redis->hGet($uid,'data');

        if ($user) {
            $user = json_decode($user,true);
        } else {
            $user = Capsule::table('mac')
                             ->select(['contract_time','duetime'])
                             ->where("MAC","=", $uid)
                             ->first();
        }

        if (is_null($user)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_UID_NOT_EXIST];
        }

        if ($user->contract_time == '0' ||
            $user->duetime == '0000-00-00 00:00:00')
        {
            $expire['expiretime'] = "2038-01-01 00:00:00";
        } else {
            $expire['expiretime'] = $user->duetime;
        }

        return ['status' => true, 'data' => $expire];
    }

    /**
     * DVB 注册
     * @return array
     */
    public function signup()
    {
        //签名
        $sign = $this->post('sign');
        $SN = $this->post('sn');
        $MAC = $this->uid;

        $KEY = "topthinker-topertv";

        //校验签名
        AES::setKEY(substr(md5($MAC.$KEY.$SN),0,16));
        $Encrypt = AES::encrypt($MAC .'|'. $SN);

        if ($sign !== $Encrypt) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_INVALID_SIGN];
        }

        $user = Capsule::table('mac')
                            ->where("mac",'=', $MAC)
                            ->first();

        if (is_null($user)) {
            $macData = array('MAC' => $MAC,'SN' => $SN, 'use_flag' => 0,'regtime' => date('Y-m-d H:i:s',time()),'contract_time'=>'10 year');
            Capsule::table('mac')->insert($macData);
            return ["status" => true, 'data' => 'register success'];
        } else {
            return ["status" => false, 'code' => ErrorCode::$RES_ERROR_UID_RIGISTERED];
        }

    }

    public function getInfo()
    {
        $from = $this->post('from', 'box');
        return $from == 'box' ? $this->getInfoFromBox() : $this->getInfoFromPhone();
    }

    /**
     * 获取用户信息 (盒子)
     */
    public function getInfoFromBox()
    {
        $data = Capsule::table('mac')
                        ->where('MAC', '=', $this->uid)
                        ->first();

        if (is_null($data)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        // 查询直播是否过期
        $user = [
            'id' => $data->id,
            'username' => $data->MAC,
            'email' => '',
            'created_at' => $data->regtime,
            'updated_at' => $data->logintime,
            'is_vip' => false,
            'vip_expire_time' => $data->duetime == '0000-00-00 00:00:00' ? 'unlimited' : $data->duetime,
            'identity_type' => 0
        ];

        return ['status' => true, 'data' => $user];
    }

    /**
     * 获取用户信息（手机）
     */
    public function getInfoFromPhone()
    {
        $data = Capsule::table('yii2_user')
                         ->where('username', '=', $this->uid)
                         ->first();

        if (is_null($data)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        // 更新用户状态
        if ($data->vip_expire_time >= time()) {
            $is_vip = true;
            $vip_expire_time = $data->vip_expire_time;
        } else {
            $is_vip = false;
            $vip_expire_time = $data->vip_expire_time;
        }

        // 查询直播是否过期
        $user =  [
            'id' => $data->id,
            'username' => $data->username,
            'email' => $data->email,
            'created_at' => $data->created_at,
            'updated_at' => $data->updated_at,
            'is_vip' => $is_vip,
            'vip_expire_time' => $vip_expire_time,
            'identity_type' => $data->identity_type
        ];

        return ['status' => true, 'data' => $user];
    }

}