<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/3
 * Time: 11:04
 */

namespace App\Controller;

use App\Components\cache\Redis;
use App\Components\encrypt\Token;
use App\Components\helper\ArrayHelper;
use App\Service\ott\ottbase;
use Workerman\Protocols\Http;

class PlayController extends BaseController
{
    public $functions = array('tvnet','viettel','sohatv', 'thvl', 'hoabinhtv', 'v4live', 'migu','sohulive','hplus','newmigu','haoqu','tencent','vtv','ott','local','youtube');
    public $check_switch = false;

    /**
     * 取视频的各种清晰度的 带音频的播放地址列表
     * @return string
     */
    public function playlist($id)
    {
        $sign = $this->request->get('sign');
        $path = '/playlist/' . $id;
        echo Token::generate($path, $this->request->ip(), 1800);
        $result = Token::validate($sign, $path, $this->request->ip());
        if ($result == false) {
            return ':(';
        }

        $data = ArrayHelper::toArray($this->request->get());
        $data['name'] = $id;

        return $playList = $this->resolve($data);
    }

    /**
     * 取视频直接播放地址 然后重定向到真正的播放地址
     * @return string
     */
    public function index($name = '')
    {
        $uid = isset($this->request->get()->uid) ? $this->request->get()->uid : '';
        if (isset($this->request->get()->getip)) {
            return $this->request->ip();
        }

        $data = ArrayHelper::toArray($this->request->get());

        // 不带鉴权模式
        // http://192.168.0.11:12389?header=hplus&name=vtv1_hd&cdn=1
        if (strpos($this->request->server('REQUEST_URI'), 'play') === false) {
            $play = isset($this->request->get()->header) ? $this->request->get()->header : '';
            if (empty($play)) {
                return '';
            }
        } else {
            // 带鉴权模式
            // http://192.168.0.11:12389/play/vtv1?resolve=hplus&sign=b4e2a6e01539680519
            $data['header'] = $this->request->get('resolve');
            $data['name'] = $name;
        }

        $url = $this->play($uid,$data);

        if ($url) {
            Http::header("location:{$url}");
        }

        return ':(';
    }

    /**
     * 取多种清晰度的播放地址
     * @param $data
     * @return bool|mixed|string
     */
    public function resolve($data)
    {
        $redis  = Redis::singleton();
        $redis->select(Redis::$REDIS_OTT_URL);
        $resolveClassName = $this->request->get('resolve');
        $resolveClass = $this->newObject($resolveClassName);

        if (is_subclass_of($resolveClass,ottbase::class)) {
            $key = $resolveClassName . '-playlist-';
            $params = $resolveClass->getResolveKey();
            foreach ($params as $_key) {
                if (isset($data[$_key])) {
                    $key .= ("-" . $data[$_key]);
                }
            }

            $playList = $redis->get($key);
            if ($playList == false){
                $playList = $resolveClass->getPlayList($data);
                if (!empty($playList)) {
                    $expireTime = $resolveClass->getExpiretime() ? $resolveClass->getExpiretime() : 3600;
                    $redis->set($key, json_encode($playList));
                    $redis->expire($key,$expireTime);
                }
            } else {
                $playList = json_decode($playList, true);
                if (empty($playList)) {
                    return false;
                }
            }

            return $playList;
        }

        //该类不存在 或者没有集成
        return false;
    }

    /**
     * @param $uid
     * @param $data
     * @return bool|mixed|string
     */
    public function play($uid,$data)
    {
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