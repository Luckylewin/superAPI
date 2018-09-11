<?php

namespace App\Service\ott;


use GuzzleHttp\Client;

class migu extends ottbase
{
    public $key = array('name');
    public $expireTime = 5400;
    public function getKey()
    {
        return $this->key;
    }

    public function getExpireTime()
    {
        return $this->expireTime;
    }
    //http://www.zhiboo.net/
	private $name = array(
		'cctv1' => 'http://static.101fk.com/file/pd_ds.php?name=hsj&id=608807427',	
		'cctv2' => 'http://static.101fk.com/file/pd_ds.php?name=hsj&id=608807428',
		'cctv3' => 'http://static.101fk.com/file/pd_ds.php?name=hsj&id=624878271',
		'cctv5' => 'https://1.jrszhibo.com/cctv5/cctv51.js',
		
	);
    public function getUrl($data)
    {

    	$index = $data['name'];
    	//var_dump($index);

    	$source = $this->name[$index];
    	//var_dump($source);

        $client = new Client();
    	$str = $client->get($source);
    	//var_dump($str);
    	
    	$url="";
    	if ($data['name']=="cctv5"){
    	    if (preg_match('/decodeURIComponent\(\'(.+)\'\)/', $str, $match)){
    	        $url = urldecode($match[1]);
    	    }
    	}
    	else{
    	    if (preg_match('/file:\'(.+)\', provider/', $str, $match)){
    	        $url = urldecode($match[1]);
    	    }
    	}
    	var_dump($url);
    	return $url;
    }

    /*   
    function get_redirect_url($url){
        $header = get_headers($url, 1);
        var_dump($header);
        if (strpos($header[0], '301') !== false || strpos($header[0], '302') !== false) {
            if(is_array($header['Location'])) {
                return $header['Location'][count($header['Location'])-1];
            }else{
                return $header['Location'];
            }
        }else {
            return $url;
        }
    }
    */
    
    function get_redirect_url($url, $referer='', $timeout = 10) {
        $redirect_url = false;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);//不返回请求体内容
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);//允许请求的链接跳转
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: */*',
        'User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)',
        'Connection: Keep-Alive'));
        if ($referer) {
            curl_setopt($ch, CURLOPT_REFERER, $referer);//设置referer
        }
        $content = curl_exec($ch);
        if(!curl_errno($ch)) {
            $redirect_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);//获取最终请求的url地址
        }
        return $redirect_url;
    }
         
}
