<?php

namespace App\Service\ott;

class hoabinhtv extends ottbase
{

    public $key = array('name','cdn');
    public $expireTime = 5400;

    public function getKey()
    {
        return $this->key;
    }

    public function getExpireTime()
    {
        return $this->expireTime;
    }

    //http://10.0.0.200:12388/?header=hoabinhtv
	//http://hoabinhtv.vn/tvonline/testhbtv.php
    public function getUrl($data){
    	
    	$source = "http://hoabinhtv.vn/tvonline/testhbtv.php";
    	//var_dump($source);
    	$str=$this->curl->exec(array(
    			'url' => $source,
    			'method' => 'get',
    			'referer' => 'http://thvl.vn/'
    	));
    	//var_dump($str);
    	if (preg_match('/(rtmp.+)\'/', $str, $match))
    		$url = $match[1];
    	//var_dump($url);
    	return $url;
    }       
       
    
}
