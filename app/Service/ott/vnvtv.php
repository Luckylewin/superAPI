<?php
/**
 * Created by PhpStorm.
 * User: sz
 * Date: 2017/8/29
 * Time: 15:39
 */
namespace App\Service\ott;

use Snoopy\Snoopy;

class vnvtv extends ottbase
{
    public $key = array('name');
    public $expireTime = 86400;
    public $url  = "http://vn.tvnet.gov.vn";
    public $list = array(
        'vtv1' => '/kenh-truyen-hinh/1011/vtv1',
        'vtv2' => '/kenh-truyen-hinh/1010/vtv2',
        'vtv3' => '/kenh-truyen-hinh/1013/vtv3',
        'vtv4' => '/kenh-truyen-hinh/1009/vtv4',
        'vtc1' => '/kenh-truyen-hinh/1007/vtc1',
        'vtc16' => '/kenh-truyen-hinh/1018/vtc16',
        'netviet' => '/kenh-truyen-hinh/1006/netviet',
        'htv9' => '/kenh-truyen-hinh/1012/htv9',
        'ttxvn' => '/kenh-truyen-hinh/1019/ttxvn',
        'hn' => '/kenh-truyen-hinh/1008/hn',
        'vov1' => '/kenh-truyen-hinh/1014/vov1',
        'vov2' => '/kenh-truyen-hinh/1015/vov2',
        'vov3' => '/kenh-truyen-hinh/1016/vov3',
        'vov5' => '/kenh-truyen-hinh/1017/vov5'
    );
    public function getKey()
    {
        return $this->key;
    }

    public function getExpireTime()
    {
        return $this->expireTime;
    }

    public function getUrl($data)
    {

        $name = $data['name'];
        if (!array_key_exists($name,$this->list)) {
            return false;
        }
        $url = $this->url . $this->list[$name]."?p2p=0&re=2";
        echo "vnvtv: {$url}\n";
        $snnopy = new Snoopy();
        $snnopy->referer = $this->url;
        $snnopy->fetch($url);
        if ($snnopy->results) {
            preg_match('/data-file\S+/',$snnopy->results,$match);
            if ($match) {
                $link = $match[0];
                $link = trim(str_replace('data-file=',"",$link),'"');
                $snnopy->results = null;
                $snnopy->fetch($link);
                if ($json = $snnopy->results) {
                   $media = json_decode($json,true);
                   return isset($media[0]['url'])?$media[0]['url']: false;
                }
                echo "json为空\n";
                return false;
            }
            echo "找不到datafile\n";
            return false;
        }
        echo "没有找到直播源\n";
        return false;
    }


}