<?php
/**
 * Created by PhpStorm.
 * User: sz
 * Date: 2017/8/24
 * Time: 18:05
 */

namespace App\Service\ott;

use Snoopy\Snoopy;

class vtvgo extends ottbase
{
     public $key = array('name');
     public $expireTime = 3600;

     public $list = array(
         'vtv1'=>'xem-truc-tuyen-kenh-vtv1-1.html',
         'vtv2'=>'xem-truc-tuyen-kenh-vtv2-2.html',
         'vtv3'=>'xem-truc-tuyen-kenh-vtv3-3.html',
         'vtv4'=>'xem-truc-tuyen-kenh-vtv4-4.html',
         'vtv5'=>'xem-truc-tuyen-kenh-vtv5-5.html',
         'vtv6'=>'xem-truc-tuyen-kenh-vtv6-6.html',
         'vtv7'=>'xem-truc-tuyen-kenh-vtv7-27.html',
         'vtv8'=>'xem-truc-tuyen-kenh-vtv8-36.html',
         'vtv9'=>'xem-truc-tuyen-kenh-vtv9-39.html',
         'vtvTayNamBo' => 'http://t.cn/RWWq4ZZ'
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
             echo "not exits $name";
             return false;
         }
         $snnopy = new Snoopy();
         $snnopy->referer = "http://vtvgo.vn";
         if($name == 'vtvTayNamBo') {
             $snnopy->fetch($this->list[$name]);
         } else{
             $snnopy->fetch("http://vtvgo.vn/" . $this->list[$name]);
         }

         preg_match("/(?:addPlayer)\('[^']+/",$snnopy->results,$match);
         if ($match[0]) {
             $match = str_replace("addPlayer('","",$match[0]);
             return $match;
         }
     }

    public function curl($url,$referer)
    {
        $ch =curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        $header = array();
        curl_setopt ($ch, CURLOPT_REFERER, $referer);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_HEADER,true);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch,CURLOPT_USERAGENT,"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.86 Safari/537.36");
        $time = time();
        curl_setopt($ch,CURLOPT_COOKIE,"__cfduid=d6f408a3d72764ea1793fb1ab18c0d5b4{$time}; PHPSESSID=82c75b2gko39q262c6d7beae17;");
        $content = curl_exec($ch);
        if ($this->debug) {
            print_r(curl_getinfo($ch));
        }
        if (curl_error($ch)) {
            echo "curl出错\n";
            var_dump(curl_error($ch));
            return false;
        }
        return $content;
    }
}