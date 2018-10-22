<?php

namespace App\Service\ott;

class thvl extends ottbase
{
    public $key = array('name');
    public $expireTime = 3600;
    //http://10.0.0.6:12388/?header=thvl&name=rtmp&cdn=1&uid=287994000002
	//http://thvl.vn/jwplayer/?l=rtmp&i=http://thvl.vn/wp-content/uploads/2014/12/THVL1Online.jpg&w=640&h=360&a=0
    public function getKey()
    {
        return $this->key;
    }

    public function getExpireTime()
    {
        return $this->expireTime;
    }

    public function getUrl($data){
    	
    	$source = "http://thvl.vn/jwplayer/?l={$data['name']}&i=http://thvl.vn/wp-content/uploads/2014/12/THVL1Online.jpg&w=640&h=360&a=0";
    	var_dump($source);

        $client = $this->getHttpClient();
        $response = $client->get($source, $this->setHeader([
            'referer' => 'http://thvl.vn/'
        ]));
        $response = $response->getBody()->getContents();

    	//var_dump($str);
    	if (preg_match('/file: \"(.+m3u8)/', $response, $match)) {
    	    return $url = $match[1];
        }

        return false;
    }
}
