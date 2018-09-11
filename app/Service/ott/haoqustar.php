<?php
/**
 * Created by PhpStorm.
 * User: sz
 * Date: 2017/8/2
 * Time: 11:26
 */

namespace App\Service\ott;

use Snoopy\Snoopy;

class haoqustar extends ottbase
{
    public $key = array('name','cdn');
    public $expireTime = 3600;
    public $list;

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
       echo "haoqustar" . $data['name'];
       return  $url = $this->_Snnopy($data['cdn'],"");
    }

    /**
     * 资源链接
     * @param $id
     * @return mixed
     */
    public function _Snnopy($id,$uri)
    {
        $interface = "http://www.haoqu.net/e/extend/tv.php?id={$id}";
        $snnopy = new Snoopy();
        $snnopy->agent = "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.86 Safari/537.36";
        $snnopy->referer = "http://www.haoqu.net".$uri;
        $snnopy->fetch($interface);
        $data = iconv("gbk","utf-8",$snnopy->results);
        preg_match("/signal\s+=\s+\S+/",$data,$match);
        if (isset($match[0])) {
            $match = explode("$",$match[0]);
            $url = $match[1];
            if (preg_match('/player.haoqu.net/',$url)) {
                echo "站外直播 没有播放源\n";
                return false;
            }
            if(preg_match('/m3u8/',$url)) {
                return $url;
            }
            if (isset($match[1]) && strpos($match[1],'http') !== false) {
                $url = $match[1];
                $snnopy->referer = $interface;
                $snnopy->fetch($url);
                $m3u8 = $snnopy->results;
                $p = '/(?<=var u = ").*(?=")/';
                if (!empty($m3u8) && preg_match($p,$m3u8,$m)) {
                    return $m[0];
                }
                echo "\匹配失败\n";
            }
        }
        echo "没有匹配到m3u8\n";
        return false;
    }



}