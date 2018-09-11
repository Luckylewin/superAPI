<?php

namespace App\Service\ott;

class viettel extends ottbase
{
    public $key = array('name','cdn');
    public $expireTime = 60;
    public function getKey()
    {
        return $this->key;
    }

    public function getExpireTime()
    {
        return $this->expireTime;
    }
    //http://10.123.15.233:12388/?header=viettel&name=46&cdn=1&uid=287994000002
	//http://otttv.viettel.com.vn/api1/watches/handheld/live/prepare
	//{"assetId":"47","filename":"47.m3u8","manifestType":"HLS","regionId":"GUEST","userId":"01635172631","version":1}
	//http://27.67.64.6:18080/47_2.m3u8?AdaptiveType=HLS&VOD_RequestID=6ef1530c-b2e8-4c85-a2ae-656be48641d5&TIMESHIFT=0
    public function getUrl($data){
    	$d['assetId'] = $data['name'];
    	$d['filename'] = $data['name']."m3u8";
    	$d['manifestType'] = "HLS";
    	$d['regionId'] = "GUEST";
    	$d['userId'] = "01635172631";
    	$d['version'] = 1;
    	
    	
    	$source = "http://otttv.viettel.com.vn/api1/watches/handheld/live/prepare";
    	$str=$this->curl->exec(array(
    			'url'=>$source,
    			'method'=>'post',
    			'post'=>json_encode($d)
    	));
    	$url = "";
    	//var_dump($str);
    	$s = json_decode($str, true);
    	//var_dump($s);
    	if ($s){
    		if (isset($data['cdn'])&&$data['cdn']>=2){
    			$url = "http://{$s['glbAddress'][1]}/{$data['name']}_{$data['cdn']}.m3u8?AdaptiveType=HLS&VOD_RequestID={$s['requestId']}&TIMESHIFT=0";
    		}
    		else 
    			$url = "http://{$s['glbAddress'][0]}/{$data['name']}_{$data['cdn']}.m3u8?AdaptiveType=HLS&VOD_RequestID={$s['requestId']}&TIMESHIFT=0";
    	}
    	//var_dump($url);
    	return $url;
    }       
       
    
}
