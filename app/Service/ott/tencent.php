<?php
/**
 * Created by PhpStorm.
 * User: sz
 * Date: 2017/8/2
 * Time: 11:26
 */


namespace App\Service\ott;

use App\Components\http\MyCurl;

class tencent extends ottbase
{
    //http://info.zb.video.qq.com/?cnlid=100001400&host=qq.com&cmd=2&qq=0&stream=1&sdtfrom=113&callback=jsonp9&0.5782554004233542
    public $key = array('name');
    public $expireTime = 5400;

    public $list = array(
        'qhws' => 'qinghaiweishi',
        'nmgws' => 'neimengguweishi',
        'hljws' => 'heilongjiangweishi',
        'ynws' => 'yunnanweishi',
        'sxws' => 'sxtvs',
        'gzws' => 'guizhouweishi',
        'scws' => 'sichuanweishi',
        'tjws' => 'tianjinweishi',
        'hbws' => 'hubeiweishi',
        'szws' => 'shenzhenweishi'
    );

    public $cnlid = array(
        'qhws' => '100101600',
        'nmgws' => '100103800',
        'hljws' => '100105501',
        'ynws' => '100104400',
        'sxws' => '100006400',
        'gzws' => '100102800',
        'scws' => '100001400',
        'tjws' => '100003900',
        'hbws' => '100104800',
        'szws' => '100003601'
    );
    public function getKey()
    {
        return $this->key;
    }

    public function getExpireTime()
    {
        return $this->expireTime;
    }
    /**
     * 获取一个腾讯卫视 zhibo.cc 直播资源链接
     * @param $data
     * @return bool|mixed
     */
    public function getUrl($data)
    {
        echo "tencent: {$data['name']}\n";
        $channelName = $data['name'];
        if (!array_key_exists($channelName,$this->cnlid)) {
            echo "没有这个台:( \n";
            return false;
        }
        $url = $this->_geturl($channelName);
        $url = $this->_Snnopy($url);
        return $url ? $url : false;
    }

    public function _geturl($name)
    {
        $cnlid = $this->cnlid[$name];
        $rand = $this->randFloat(0,1);
        return $url =  "http://info.zb.video.qq.com/?cnlid={$cnlid}&host=qq.com&cmd=2&qq=0&stream=1&sdtfrom=113&callback=jsonp9&{$rand}";
    }

    public function _Snnopy($url)
    {
        $client = $this->getHttpClient();
        $response = $client->get($url, $this->setHeader([
            'referer' => "http://info.zb.video.qq.com"
        ]));
        $response = $response->getBody()->getContents();

        $data = str_replace("jsonp9","",$response);
        $data = trim($data,"(");
        $data = trim($data,")");
        $data = json_decode($data,true);
        if (is_array($data)) {
          if (isset($data['backurl_list'][0]['url'])) {
              return $data['backurl_list'][0]['url'];
          }elseif(isset($data['backurl_list'][1]['url'])) {
              return $data['backurl_list'][1]['url'];
          }
        }
            echo "没有找到播放源";
            return false;
    }

    public function randFloat($min=0, $max=1)
    {
        return ($min + mt_rand()/mt_getrandmax() * ($max-$min) ) . mt_rand(10,99);
    }


}


