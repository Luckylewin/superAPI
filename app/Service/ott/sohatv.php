<?php

namespace App\Service\ott;

use App\Components\http\MyCurl;

class sohatv extends ottbase
{
    public $key = array('name');
    public $expireTime = 21600;
	//http://play.sohatv.vn/?v=dnR2MQ==&t=20170319184451&autoplay=true
	//http://10.0.0.6:12388/?header=sohatv&name=vtv2&cdn=1&uid=287994000002
	private $name = array(
		'vtv1' => 'dnR2MQ==',
		'vtv1hd' => 'dnR2bGl2ZQ==',
		'vtv2' => 'dnR2Mg==',
		'vtv3' => 'dnR2Mw==',
		'vtv4' => 'dnR2NA==',
		'vtv5' => 'dnR2NQ==',
		'vtv5hd' => 'dnR2NWtt',
		'vtv6' => 'dnR2Ng==',
		'vtv7' => 'dnR2Nw==',		
		'vtv8' => 'dnR2OA==',
		'vtv9' => 'dnR2OQ==',
        'vtv5hd1' => 'dnR2NXRu',
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
        $index = $data['name'];
    	$timestamp = date("YmdHis");;
    	$source = "http://play.sohatv.vn/?v={$this->name[$index]}&t={$timestamp}&autoplay=true";

    	$client = $this->getHttpClient();
    	$str = $client->get($source, $this->setHeader([
            'referer' => 'http://vtv.vn/'
        ]));

    	$url="";

    	if (preg_match('/live=(.+m3u8)/', $str, $match)) {
    		$url = "http:".urldecode($match[1]);
    	} else if (preg_match('/live: \'(.+m3u8)/', $str, $match)) {
                $url = "http:".urldecode($match[1]);
        } else if (preg_match('/\[\{\"src.+\]/', $str, $match)) {
                $json = json_decode($match[0], true);
                $url = $json[0]['src'];
	    }

        return str_replace('http:http://','http://',$url);
    }       
       
    
}
