<?php

namespace App\Service\ott;

class v4live extends ottbase
{
    public $key = array('name','cdn');
    public $expireTime = 600;
    //http://tv.101vn.com/ok/vtc/vtc7_2.php
	private $name = array(
	    //'vtv1' => 'http://tv.101vn.com/ok/vtv/vtv13.php',
		'vtc7' => 'http://tv.101vn.com/ok/vtc/vtc7_2.php',	
		'vtc9' => 'http://tv.101vn.com/ok/vtc/vtc9_1.php',
		'vtc13' => 'http://tv.101vn.com/ok/vtc/itv_1.php',
		
	);
    public function getKey()
    {
        return $this->key;
    }

    public function getExpireTime()
    {
        return $this->expireTime;
    }

    public function getUrl($data){
    	$index = $data['name'];
    	//var_dump($index);

    	$source = $this->name[$index];
    	//var_dump($source);
    	
    	$str = $this->curl->exec(array(
    			'url' => $source,
    			'method' => 'get',
    	));
    	//var_dump($str);
    	
    	$url="";
    	if (preg_match('/addPlayer\(\'(.+=.)/', $str, $match)){
    		$temp_url = urldecode($match[1]);
    		$url = $this->get_redirect_url($temp_url);
    		if (!preg_match('/v4live/', $url, $match)){
    		    return '';
    		}
    	}
    	//var_dump($url);
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
