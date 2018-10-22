<?php

namespace App\Service\ott;

class tvnet extends ottbase
{
    public $key = array('name');
    public $expireTime = 900;

    public function getKey()
    {
        return $this->key;
    }

    public function getExpireTime()
    {
        return $this->expireTime;
    }
	//vtv1,2,3,4 vtc1,10,16 htv9 hanoi1 ttxvn
	//http://10.123.15.233:12388/?header=tvnet&name=vtv1&cdn=1&uid=287994000002
    //http://118.107.85.21:1337/get-stream.json?p=smil:vtv1.smil&t=l&ott=Web_Other&ipz=118.107.85.37
    public function getUrl($data){
    	$source = "http://118.107.85.21:1337/get-stream.json?p=smil:{$data['name']}.smil&t=l&ott=Phone_Android&ipz=118.107.85.37";
    	//var_dump($source);
    	$str = $this->curl->exec(array(
    			'url'=>$source,
    			'method'=>'get'
    	));
    	
    	$url = "";
    	$s = json_decode($str, true);
    	if ($s){
    		if (isset($data['def'])&&$data['def']==2){
    			$url =  $s[1]['url'];
    		}
    		else 
    			$url =  $s[0]['url'];
    	}
    	//var_dump(json_decode($str, true));
    	return $url;
    }       
       
    
}
