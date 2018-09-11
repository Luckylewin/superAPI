<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/3
 * Time: 11:44
 */

namespace App\Components\cache;

use Breeze\Config;

class Redis
{
    public static $REDIS_OTT_PARADE              =          0;
    public static $REDIS_DEVICE_STATUS			 =			1;
    public static $REDIS_EPG		             =			2;
    public static $REDIS_COMMON_REPO	         =			3;
    public static $REDIS_PROTOCOL          		 =			4;
    public static $REDIS_VOD_ALBUM          	 =			5;
    public static $REDIS_VOD_INFO          		 =			6;
    public static $REDIS_VOD_DOWNLOAD          	 =			7;
    public static $REDIS_OTT_URL		         =			8;
    public static $REDIS_APP_MARKET              =          9;
    public static $REDIS_CHINA_IP                =          11;
    public static $REDIS_AUTH_TOKEN              =          12;
    public static $REDIS_ADVER_DATA              =          13;
    public static $REDIS_API_LOG          =          14;
    public static $REDIS_IKS_USER_LIST           =          15;

    public $redis;
    protected static $instance;

    public static function singleton()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    function __construct()
    {
        $db_config = Config::get('database.redis');
        $this->redis = new \Redis();
        try{
            $this->redis->connect($db_config["host"], $db_config["port"]);
            $this->redis->auth($db_config["password"]);
        } catch(\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function getRedis()
    {
        return $this->redis;
    }

    //带生存时间的写入值
    public function setex($key,$time,$data)
    {
        return $this->redis->setex($key,$time,$data);
    }

    public function expire($key,$time){
        return $this->redis->expire($key,$time);
    }

    public function isExists($key)
    {
        return $this->redis->exists($key);
    }

    public function select($idx)
    {
        $this->redis->select($idx);
    }

    public function keys($field)
    {
        return $this->redis->keys($field);
    }

    public function get($key)
    {
        return $this->redis->get($key);
    }

    public function set($key, $data)
    {
        return $this->redis->set($key, $data);
    }

    public function delete($key){
        $this->redis->delete($key);
        return true;
    }

    public function hGet($key, $field){
        return $this->redis->hGet($key, $field);
    }

    public function hSet($key, $field, $data){
        return $this->redis->hSet($key, $field, $data);
    }

    public function hmSet($key,array $data)
    {
        return $this->redis->hMset($key,$data);
    }

    public function hmGet($key,array $hashkeys)
    {
        return $this->redis->hMGet($key,$hashkeys);
    }

    public function hDel($key, $field){
        return $this->redis->hDel($key, $field);
    }

    public function hGetAll($key){
        return $this->redis->hGetAll($key);
    }

    public function getKeys($p){
        return $this->redis->getKeys($p);
    }

    public function getSize()
    {
        return $this->redis->dbSize();
    }

    public function flushDB()
    {
        return $this->redis->flushDB();
    }

    public function close()
    {
        $this->redis->close();
    }

    public function ttl($key)
    {
        return $this->redis->ttl($key);
    }

    public function incr($key)
    {
        return $this->redis->incr($key);
    }

    /**
     * 哈希自增
     * @param $key
     * @param $field
     * @param $number
     * @return int
     */
    public function hincrby($key,$field,$number)
    {
        return $this->redis->hIncrBy($key,$field,$number);
    }

    public function lPush($key, $value)
    {
        return $this->redis->lPush($key,$value);
    }

}