<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/3
 * Time: 11:04
 */

namespace App\Controller;


use App\Components\cache\Redis;
use App\Components\helper\ArrayHelper;
use App\Service\ott\ottbase;
use Breeze\Route;
use Workerman\Protocols\Http;

class PlayController extends BaseController
{
    public $functions = array('tvnet','viettel','sohatv', 'thvl', 'hoabinhtv', 'v4live', 'migu','sohulive','hplus','newmigu','haoqu','tencent','vtv','ott','local','youtube');
    public $check_switch = false;

    public function index()
    {
        if (isset($this->request->get()->getip)) {
            return $this->request->ip();
        }
        $play = isset($this->request->get()->header) ? $this->request->get()->header : '';
        if (empty($play)) {
            return '';
        }
        $data = ArrayHelper::toArray($this->request->get());
        $uid = isset($this->request->get()->uid) ? $this->request->get()->uid : '';
        $url = self::$play($uid,  $data);
        if ($url) {
            Http::header("location:{$url}");
        }

        return '空空如也';
    }

    public function __call($name, $arguments)
    {
        $uid = $arguments[0];
        $data = $arguments[1];
        return $this->ott($uid,$data);
    }

    /**
     * 校验单个源的有效性
     * @param $data
     * @return bool
     */
    public function validate($data)
    {
        if ($this->check_switch) {
            $secret = "topthinker";
            $expire = isset($data['e'])? $data['e'] : 0;
            $uri = isset($data['name'])? $data['name'] : 0;
            $url_md5 = isset($data['md5'])?$data['md5'] : '';
            $md5 = md5(md5($expire.$uri).$secret);
            if ($md5 != $url_md5) {
                echo "md5校验不通过\n";
                return false;
            }
            if ($expire < time()) {
                echo "链接已经过期\n";
                return false;
            }
        }
        return true;
    }
    /**
     * @param $uid
     * @param $data
     * @return bool|mixed|string
     */
    public function ott($uid,$data)
    {
        if (!$this->validate($data)) {
            return false;
        }

        $redis  = Redis::singleton();
        $redis->select(Redis::$REDIS_OTT_URL);
        $c = isset($data['class'])? $data['class'] : $data['header'];

        if ($c == 'local') {
            return false;
        }

        $class = $this->newObject($c);

        if (!$class) {
            return false;
        }

        if (is_subclass_of($class,ottbase::class)) {
            $key = $c;
            $params = $class->getKey();
            foreach ($params as $_key) {
                if (isset($data[$_key])) {
                    $key .= ("-".$data[$_key]);
                }
            }
            $url = $redis->get($key);
            if (!isset($url) || $url == false){
                $url = $class->getUrl($data);
                if (preg_match('/(http:\/\/)|(rtmp:\/\/)|(https:\/\/)/i', $url)) {
                    $expireTime = $class->getExpiretime()?$class->getExpiretime():3600;
                    $redis->set($key, $url);
                    $redis->expire($key,$expireTime);
                }
            }
            return $url;
        }
        //该类不存在 或者没有集成
        return false;
    }

    private function newObject($class)
    {
        $obj_name = "App\Service\ott\\".$class;
        try {
            $class = new \ReflectionClass($obj_name);
            return $class->newInstance();
        }catch (\Exception $e) {
            return false;
        }
    }
}