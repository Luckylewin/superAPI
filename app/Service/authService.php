<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/4/16
 * Time: 9:29
 */

namespace App\Service;

use App\Components\cache\Redis;
use App\Components\encrypt\AES;
use App\Components\helper\ArrayHelper;
use App\Exceptions\ErrorCode;
use Breeze\Http\Request;
use Illuminate\Database\Capsule\Manager as Capsule;

class authService extends common
{
    protected $clientIP;

    /**
     * authService constructor.
     * @param Request $request
     */
    public function __construct($request)
    {
        parent::__construct($request);
        $this->clientIP = $request->ip();
    }

    public function login(string $mac, int $timestamp, string $signature): array
    {
        $sn = $mac;

        if (empty($mac) || empty($timestamp) || empty($signature)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_PARAMETER_MISSING];
        }

        if (abs(time() - $timestamp) > 15) {
           return ['status' => false, 'code' => ErrorCode::$RES_ERROR_INVALID_REQUEST];
        }

        $time = (int) substr($timestamp,0, 10);
        if (abs(time() - $time) > 3600) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_INVALID_REQUEST];
        }

        $serverSign = md5(md5($mac . $timestamp) . md5('topthinker' . $timestamp));
        if ($serverSign != $signature) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_INVALID_SIGN];
        }

        try {
            $macInfo = $this->_getMacInfo($mac, $sn);
            $this->_checkIsExpire($macInfo);
            $this->_checkIsActive($macInfo);
            $tokenData = $this->_generateToken($mac);

            $macInfo['access_token']        = $tokenData['token'];
            $macInfo['access_token_expire'] = $tokenData['expire'];

            $this->_updateInfo($macInfo);

            return ['status' => true, 'data' => $macInfo];

        } catch (\Exception $e) {
            return ['status' => false, 'code' => $e->getCode()];
        }
    }


    /**
     * 获取令牌
     * @return array
     */
    public function getClientToken()
    {
        $data = $this->data;
        $uid = $this->uid;

        try {
            //检查参数
            $decryptStr = $this->_checkParams($uid, $data);

            $login_token = isset($data['login_token']) && !empty($data['login_token']) ? $data['login_token'] : null;

            //检查token是否需要重新生成
            if ($token = $this->_checkTokenUpdate($uid,$login_token)) {
                return ['status' => true, 'data' => $token];
            }

            //从redis/mysql中获取用户数据
            $SN = $this->_getSN($decryptStr);

            $macInfo = $this->_getMacInfo($uid, $SN);
            //检查用户是否过期
            $this->_checkIsExpire($macInfo);
            //检查用户是否激活
            $this->_checkIsActive($macInfo);
            // 生成token
            $tokenData = $this->_generateToken($uid);
            $macInfo['access_token'] = $tokenData['token'];
            $macInfo['access_token_expire'] = $tokenData['expire'];

            //更新用户登录信息
            $this->_updateInfo($macInfo);

            //返回新生成的token值
            return ['status' => true, 'data' => json_encode($tokenData)];

        } catch (\Exception $e){
            $this->stdout($e->getMessage(), 'ERROR');
           return ['status' => false, 'code' => $e->getCode()];
        }
    }


    /**
     * 验证token restful yii2
     * @param $uid
     * @param $token
     * @return array
     */
    public function validateTokenViaYii($uid, $token)
    {
       $user =  Capsule::table('yii2_user')
                            ->where('username','=', $uid)
                            ->first();

       if ($user->count() == false) {
           $this->stdout("用户不存在", 'ERROR');
           return ['status' => false, 'code' => ErrorCode::$RES_ERROR_UID_NOT_EXIST];
       }
       
       if ($user->access_token_expire < time()) {
           $this->stdout("token过期", 'ERROR');
           return ['status' => false, 'code' => ErrorCode::$RES_ERROR_UID_NOT_EXIST];
       }

       if ($user->access_token != $token) {
           $this->stdout("非法的token", 'ERROR');
           return ['status' => false, 'code' => ErrorCode::$RES_ERROR_INVALID_TOKEN];
       }

       return ['status' => true];
    }

    /**
     * 检验token
     * @param $MAC
     * @param $Token
     * @param bool $mode
     * @return array
     */
    public function validateToken($MAC,$Token,$mode=false)
    {
        $cache = Redis::singleton();
        $cache->getRedis()->select(Redis::$REDIS_DEVICE_STATUS);

        $RedisToken = $cache->hGet($MAC,'token');
        if ($Token != $RedisToken || !$RedisToken) {
            $this->stdout("redis找不到该token", 'ERROR');
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_INVALID_TOKEN];
        }

        //严格模式
        if ($mode) {
            //解密token
            $decryptArr = $this->_decryptToken($MAC,$Token);
            if (!$decryptArr) {
                $this->stdout("解密失败", 'ERROR');
                return ['status' => false, 'code' => ErrorCode::$RES_ERROR_INVALID_TOKEN];
            }

            $encryptMac = $decryptArr['mac'];
            $encryptTime = $decryptArr['expire'];
            $currentTime = time();

            //检查MAC地址
            if ($MAC != $encryptMac) {
                $this->stdout("MAC地址不匹配,非法的token", 'ERROR');
                return ['status' => false, 'code' => ErrorCode::$RES_ERROR_INVALID_TOKEN];
            }

            //检查时间
            if ($currentTime > $encryptTime) {
                $this->stdout("Token过期", 'ERROR');
                return ['status' => false, 'code' => ErrorCode::$RES_ERROR_TOKEN_EXPIRE];
            }

        }

        return ['status' => true];
    }

    /**
     * 简单校验accessToken
     * @param $accessToken
     * @return bool
     */
    public function validateAccessToken($accessToken)
    {
        $mac = Capsule::table('mac')
                        ->select('MAC')
                        ->where('access_token', '=', $accessToken)
                        ->first();

        if (is_null($mac)) {
            return false;
        }
        
        $info = $this->_decryptToken($mac->MAC, $accessToken);

        //判断是否过期
        if ($info['expire'] <= time()) {
            return false;
        }
        // 检查用户 ip 不一致则不通过
        if ($info['clientIP'] != $this->request->ip()) {
            // return false;
        }

        return true;
    }

    private function _decryptToken($MAC,$Token)
    {
        $Token = str_replace('*','=',$Token);
        AES::setKEY(substr(md5($MAC . AES::$_KEY), 16, 32));
        $decryptStr = AES::decrypt(base64_decode($Token));

        if ($decryptStr) {
            $decryptStr = explode('|',$decryptStr);
            return array(
                'clientIP' => $decryptStr[1],
                'mac' => $decryptStr[2],
                'expire' => $decryptStr[3]
            );
        }
        return false;
    }


    /**
     * 检查签名
     * @param $uid
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function checkSign($uid,$data)
    {
        if (!isset($data['sign']) || !isset($data['noncestr'])) {
            throw new \Exception("缺少参数",ErrorCode::$RES_ERROR_PARAMETER_MISSING);
        }

        $key = 'topthinker';
        $sign = $data['sign'];
        $noncestr = $data['noncestr'];

        AES::setKEY(substr(md5($uid.$key.$noncestr),0,16));
        if ($sign !=  AES::encrypt($uid)) {
            throw new \Exception("签名不合法",ErrorCode::$RES_ERROR_INVALID_SIGN);
        }

        return true;
    }

    /**
     * 检测参数
     * @param $uid
     * @param $data
     * @return String
     * @throws \Exception
     */
    private function _checkParams($uid,$data)
    {
        $MAC = $this->formatMac($uid);
        $time = $data['time'] ?? '';

        $currentTime = time();
        $encryptStr = $data['sign'] ?? '';

        if (empty($time) || empty($encryptStr)) {
            throw new \Exception("参数不全",ErrorCode::$RES_ERROR_INVALID_SIGN);
        }

        if ($currentTime + 5 < $time || $currentTime - $time > 3600) {
            //throw new \Exception("请求时间超时",$this->view->RES_ERROR_TIMESTAMP);
        }

        AES::setKEY(substr(md5($MAC . $time . AES::$_KEY),0,16));
        $decryptStr = AES::decrypt($encryptStr);

        if (strpos($decryptStr,'-') == false) {
            throw new \Exception("错误的签名",ErrorCode::$RES_ERROR_INVALID_SIGN);
        }

        return $decryptStr;
    }

    /**
     * 取得加密串中的SN
     * @param $string
     * @return mixed
     */
    private function _getSN($string)
    {
        $MAC_SN = explode('-',$string);
        return $SN = $MAC_SN[1];
    }


    /**
     *  判断Token是否需要重新生成
     * @param $MAC
     * @param $login_token
     * @return bool|string
     */
    private function _checkTokenUpdate($MAC,$login_token)
    {
        return false;

    }

    /**
     * 获取用户数据
     * @param $MAC
     * @param $SN
     * @return array
     * @throws \Exception
     */
    private function _getMacInfo($MAC, $SN)
    {
        $cache = $this->getRedis(Redis::$REDIS_DEVICE_STATUS);
        $macInfo = $cache->hmGet($MAC, array('MAC','SN','use_flag','duetime','contract_time','logintime'));

        if (empty($macInfo['MAC'])) {
            //redis找不到 查找数据库
            $macInfo = Capsule::table('mac')
                          ->select(['MAC','SN','use_flag','duetime','contract_time','logintime','ver'])
                          ->where([
                              ['MAC' ,'=', $MAC],
                              ['SN' ,'=', $SN],
                          ])
                          ->first();

            if (is_null($macInfo)) {
                throw new \Exception("UID不存在",ErrorCode::$RES_ERROR_UID_NOT_EXIST);
            }

            $macInfo = ArrayHelper::toArray($macInfo);
            $cache->hmSet($MAC,$macInfo);
        }

        return $macInfo;
    }

    /**
     * 判断是否过期
     * @param $macInfo
     * @return bool
     * @throws \Exception
     */
    private function _checkIsExpire($macInfo)
    {
        //判断mac是否过期
        if ($macInfo['use_flag'] == 2 || ( isset($macInfo['duetime']) && $macInfo['duetime'] != '0000-00-00 00:00:00'  && (strtotime(date('Y-m-d')) >= strtotime($macInfo['duetime'])) && $flag=true) ) {
            //uid expired
            if (isset($flag) && isset($macInfo['MAC']) && isset($macInfo['SN'])) {
                $cache = $this->getRedis(Redis::$REDIS_DEVICE_STATUS);
                $updateData = ['use_flag' => 2];
                $cache->hmSet($macInfo['MAC'],$updateData);
                Capsule::table('mac')
                        ->where([
                            ['MAC','=', $macInfo['MAC']],
                            ['SN', '=', $macInfo['SN']]
                        ])
                        ->update($updateData);
            }

            throw new \Exception("MAC用户过期",ErrorCode::$RES_ERROR_UID_EXPIRED);//mac过期
        }

        return true;
    }

    /**
     * 判断是否激活
     * @param $macInfo
     * @throws \Exception
     */
    private function _checkIsActive($macInfo)
    {
        //是否激活
        if ($macInfo['use_flag'] != 1) {
            //是否被加入黑名单
            if ($macInfo['use_flag'] == 3) {
                throw new \Exception("MAC用户被加入黑名单",ErrorCode::$RES_ERROR_UID_IS_BLACK_LIST);
            }

            //激活 更新过期时间
            $duetime = "0000-00-00 00:00:00";
            $periods = array('month','year','day');
            if ($macInfo['contract_time'] != '0') {
                foreach ($periods as $period){
                    if (preg_match("/$period/",$macInfo['contract_time'])) {
                        preg_match("/\d+/",$macInfo['contract_time'],$number);
                        $duetime = date('Y-m-d H:i:s',strtotime("+{$number[0]} $period"));
                        break;
                    }
                }
            }

            $updateData['duetime'] = $duetime;
            $updateData['use_flag'] = 1;
            $updateData['regtime'] = date('Y-m-d H:i:s');
            //"用户激活，过期时间是{$duetime}"
            if (isset($macInfo['MAC']) && isset($macInfo['SN'])) {
                $cache = $this->getRedis();
                $cache->hmSet($macInfo['MAC'],$updateData);
                Capsule::table('mac')
                        ->where([
                            ['MAC', '=', $macInfo['MAC']],
                            ['SN', '=', $macInfo['SN']]
                        ])
                        ->update($updateData);

            }
        }
    }

    /**
     * 更新用户数据
     * @param $macInfo
     */
    private function _updateInfo($macInfo)
    {
        $updateInfo = [
            'logintime' => date('Y-m-d H:i:s'),
            'access_token' => $macInfo['access_token'],
            'access_token_expire' => $macInfo['access_token_expire'],
        ];

        Capsule::table('mac')
            ->where([
                ['MAC', '=', $macInfo['MAC']],
                ['SN', '=', $macInfo['SN']]
            ])
            ->update($updateInfo);
    }

    /**
     * 生成用户token
     * @param $MAC
     * @return array
     */
    private function _generateToken($MAC)
    {
        $token       = $this->_generateAccessToken($MAC);
        $login_token = $this->_generateLoginToken($MAC);

        return [
            'token'       => $token['token'],
            'expire'      => $token['expire'],
            'login_token' => $login_token,
        ];
    }

    private function _generateAccessToken($MAC)
    {
        $valid_time = 3600 * 6;
        $cache = $this->getRedis(Redis::$REDIS_DEVICE_STATUS);

        if (time() - strtotime($cache->hGet($MAC,'logintime')) < 60) {
            $expire_time = time() + $valid_time - 70;
            return ['token' => $cache->hGet($MAC,'token'),'expire' => $expire_time];
        }

        $expire_time = $valid_time + time();
        AES::setKEY(substr(md5($MAC . AES::$_KEY),16,32));
        $encrypt = AES::encrypt(mt_rand(111,999)."|".$this->clientIP.'|'.$MAC.'|'.$expire_time);
        $token = base64_encode($encrypt);
        $token = str_replace('=','*', $token);

        $cache->hSet($MAC,'token', $token);
        $cache->hSet($MAC,'logintime', date('Y-m-d H:i:s'));
        $cache->expire($MAC,$valid_time);

        return ['token' => $token,'expire' => $expire_time];
    }

    private function _generateLoginToken($MAC)
    {
        $login_token = AES::encrypt( base64_encode(mt_rand(111,999) . "|" . $MAC . "|" . mt_rand(111,999)));
        $this->getRedis()->hSet($MAC, 'login_token', $login_token);
        return $login_token;
    }

    public function setIP($ip)
    {
        $this->clientIP = $ip;
        return $this;
    }


    public function judgeMacExist($mac)
    {
        $result = Capsule::table('mac')
                            ->where('mac', '=', $mac)
                            ->exists();

        if ($result == false) {
            throw new \Exception('用户不存在', ErrorCode::$RES_ERROR_UID_NOT_EXIST);
        }

        return true;
    }


}
