<?php
/**
 * Created by PhpStorm.
 * User: sz
 * Date: 2017/8/2
 * Time: 11:26
 */

namespace App\Service\ott;


use App\Components\http\MyCurl;

class haoqu extends ottbase
{
    public $key = array('name');
    public $expireTime = 5400;

    public  $_keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
    public  $para = array(2,3,5,15,32,12,16,29,15,18,19,1,17,15,28,26,23,31,34,65,95,36,200,145,168,179,135,126,201,111,110,109);
    public  $para1 = array(71,2,199,3,196,115,6,150,112,51,127,173,171,77,3,82,170,41,66,102,143,96,212,26,48,104,102,145,171,163,211,27);
    public $list = array('cctv1','cctv2','cctv3',
                     'cctv4','cctv5','cctv5plus',
                     'cctv6', 'cctv7','cctv8','cctv9',
                     'cctv10','cctv11','cctv12',
                     'cctv13','cctvchild','cctv15','cctvjilu'
    );
    public $url = "https://cc.haoqu.net/cctv/cctv.php?id=";

    public function getKey()
    {
        return $this->key;
    }

    public function getExpireTime()
    {
        return $this->expireTime;
    }

    /**
     * 获取一个CCTV直播资源链接
     * @param $data
     * @return bool|mixed
     */
    public function getUrl($data)
    {
        $data['name'] = isset($data['name'])? $data['name']: '';
        echo "haoqu: {$data['name']}\n";
        $channelName = $data['name'];
        if (!in_array($channelName,$this->list)) {
            return false;
        }
        $url = $this->_Snnopy($channelName);
        return $url ? $url : false;
    }

    /**
     * @param $channelName
     * @return bool|string
     */
    public function _Snnopy($channelName)
    {
        $url = $this->url . $channelName;
        // $snnopy->fetch($url);
        // $data = $snnopy->results;
        $curl = new MyCurl();
        $opts = array(
            'referer' => 'http://www.haoqu.net',
            'user_agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.86 Safari/537.36'
        );
        $curl->setOptions($opts);
        $data = $curl->exec(array(
            'url'=>$url,
            'method'=>'get'
        ));
        preg_match("/link=decipher.*/",$data,$match);
        if (!empty($match[0])) {
            $data = $match[0];
            preg_match('/".*"/',$data,$match);
            if (!empty($match[0])) {
               $encryptStr = trim($match[0],'"');
            }
        }
        if (!isset($encryptStr)) {
            echo "没有匹配到加密字符串";return false;
        }
        return $this->decipher($this->decode($encryptStr));
    }

    public function encode($input) {
        $output = "";
        $chr1 = $chr2 = $chr3 = $enc1 = $enc2 = $enc3 = $enc4 = 0;
        $i = 0;
        $input = $this->_utf8_encode($input);
        while ($i <= strlen($input)) {
            $chr1 = $this->charCodeAt($input,$i++);
            $chr2 = $this->charCodeAt($input,$i++);
            $chr3 = $this->charCodeAt($input,$i++);
            $enc1 = $chr1 >> 2;
            $enc2 = (($chr1 & 3) << 4) | ($chr2 >> 4);
            $enc3 = (($chr2 & 15) << 2) | ($chr3 >> 6);
            $enc4 = $chr3 & 63;
            if (!is_numeric($chr2)) {
                $enc3 = $enc4 = 64;
            } else if (!is_numeric($chr3)) {
                $enc4 = 64;
            }
            $output = $output .
                substr($this->_keyStr,$enc1,1) . substr($this->_keyStr,$enc2,1) .
                substr($this->_keyStr,$enc4,1) . substr($this->_keyStr,$enc4,1);

        }
        return $output;
    }

    public function decode($input)
    {
        $output = "";
        $chr1 = $chr2 = $chr3 = null;
        $enc1 = $enc2 = $enc3 = $enc4 =null;
        $i = 0;
        $input = preg_replace('/[^A-Za-z0-9\+\/\=]/', "", $input);

        while ($i < strlen($input)) {

            $enc1 = strpos($this->_keyStr, substr($input,$i++,1));

            $enc2 = strpos($this->_keyStr, substr($input,$i++,1));
            $enc3 = strpos($this->_keyStr, substr($input,$i++,1));
            $enc4 = strpos($this->_keyStr, substr($input,$i++,1));

            $chr1 = ($enc1 << 2) | ($enc2 >> 4);
            $chr2 = (($enc2 & 15) << 4) | ($enc3 >> 2);
            $chr3 = (($enc3 & 3) << 6) | $enc4;



            $output = $output . chr($chr1);

            if ($enc3 != 64) {
                $output = $output . chr($chr2);
            }
            if ($enc4 != 64) {
                $output = $output . chr($chr3);
            }

        }

        $output = $this->_utf8_decode($output);
        return $output;
    }

    public function _utf8_decode($utftext)
    {
        $string = "";
        $i = 0;
        $c = $c1 = $c2 = 0;
        while ( $i < strlen($utftext) ) {
            $c = $this->charCodeAt($utftext, $i);
            if ($c < 128) {
                $string .= chr($c);
                $i++;
            } else if(($c > 191) && ($c < 224)) {
                $c2 = $this->charCodeAt($utftext, $i+1);
                $string .= chr((($c & 31) << 6) | ($c2 & 63));
                $i += 2;
            } else {
                $c2 = $this->charCodeAt($utftext, $i+1);
                $c3 = $this->charCodeAt($utftext, $i+2);
                $string .= chr((($c & 15) << 12) | (($c2 & 63) << 6) | ($c3 & 63));
                $i += 3;
            }
        }
        return $string;
    }

    public function _utf8_encode($input)
    {

    }

    public function charCodeAt($str, $index)
    {
        $char = mb_substr($str, $index, 1, 'UTF-8');

        if (mb_check_encoding($char, 'UTF-8'))
        {
            $ret = mb_convert_encoding($char, 'UTF-32BE', 'UTF-8');
            return hexdec(bin2hex($ret));
        }
        else
        {
            return null;
        }
    }

    private function str_split_unicode($str, $l = 0) {
        if ($l > 0) {
            $ret = array();
            $len = mb_strlen($str, "UTF-8");
            for ($i = 0; $i < $len; $i += $l) {
                $ret[] = mb_substr($str, $i, $l, "UTF-8");
            }
            return $ret;
        }
        return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
    }

    public function decipher($buf)
    {
        /*
         if(strpos($buf, "http://") >= 0)
         {
             return $buf;
         }*/
        $buf2 = $this->str_split_unicode($buf);

        for($i = 0;$i<count($this->para1);$i++)
        {
            if($this->para1[$i] < count($buf2))
            {
                $char_i = $buf2[$this->para1[$i]];
                $int_i = $this->charCodeAt($char_i,0);
                $int_i -= $i;
                if($int_i <= 1 && $i > 1)//
                {
                    $int_i = $int_i + 127;
                }
                if($this->para1[$i] < count($buf2))
                {
                    $buf2[$this->para1[$i]] = chr($int_i);
                }
            }
        }

        $new_url = implode("", $buf2);
        if(strpos($new_url, "http://") >= 0)
        {
            return $new_url;
        }
        else
        {
            //return decipher2(buf,para2);
            return $buf;
        }
    }

}