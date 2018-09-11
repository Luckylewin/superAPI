<?php

namespace App\Service\ott;

class hplus extends ottbase
{
    public $key = array('name');
    public $expireTime = 5400;
    //http://10.123.15.233:12388/?header=hplus&name=htv1&cdn=1&uid=287994000002
	//http://api.hplus.com.vn/detail?lang_id=2&device=android&version=1.4&id=2631
	//post lang_id=2&device=android&version=1.4&id=2631&
	    private $name = array(
        'htv1' => '2631',
        'htv2' => '2630',
        'htv2hd' => '2669',
        'htv3' => '2535',
        'htv4' => '2528',
        'htv7' => '10036',
        'htv9' => '10037',
        'htv_coop' => '31973',
        'htvc_shopping' => '2265',
        'htvc_phim' => '2397',
        'htvc_phimhd' => '2399',
        'htvc_thuan_viet' => '2396',
        'htvc_thuan_viethd' => '2398',
        'htvc_fbnc' => '2395',
        'htvc_ca_nhac' => '2264',
        'htvc_the_thao' => '2400',
        'htvc_the_thaohd' => '4009',
        'htvc_plus' => '2395',
        'htvc_gia_dinh' => '2394',
        'htvc_phu_nu' => '2393',
        'htvc_du_lich' => '2328',
        'antv' => '73411',
        'vtv1_hd' => '52524',
        'vtv1' => '2128',
        'vtc1' => '50075',
        'vtc4' => '2057',
        'vtc7' => '2060',
        'vtc8' => '2056',
        'vtc9' => '2052',
        'vtc10' => '2051',
        'vtc11' => '50071',
        'vtc14' => '50074',
        'vtc16' => '50072',        
        'Kien_Giang_1' => '774',
        'Kien_Giang_2' => '6917',        
        'NBTV' => '868',
        'Bac_Lieu' => '872',
        'Hai_Duong' => '871',
        'Long_An_34' => '873',
        'Binh_Phuoc_1' => '877',
        'Vinh_Long_1' => '899',
        'Vinh_Long_2' => '52406',
        'Bac_Giang' => '1210',
        'Soc_Trang' => '1786',
        'Quang_Nam' => '1787',
        'Lao_Cai' => '1789',
        'Ca_Mau' => '1903',
        'Binh_Dinh' => '1955',
        'Lang_Son' => '1956',
        'Khanh_Hoa' => '1979',
        'Can_Tho' => '1980',
        'Tay_Ninh' => '1981',
        'Ba_Ria' => '1982',
        'Da_Nang_1' => '3268',
        'Da_Nang_2' => '53529',
        'Tra_Vinh' => '3254',
        'Kien_Giang_1' => '6917',
        'An_Giang_TV1' => '51818',
        'Nhan_Dan' => '50201',
        'Ben_Tre' => '61299',
        'Lam_Dong' => '55163',
        'Binnh_Thuan' => '53530',
        'AZSHOP' => '70471', 
        'FM95_6' => '60543', 
        'FM99_9' => '60542',
        'AM610' => '60541',
        'DRT' => '1785',
        'PTD' => '1784',
        'Ha_Tinh' => '7796',
        'Dong_Thap'=> '1788',
        'Binh_Duong_1'=> '1907',
        'Nam_Dinh'=> '1951',
        'Mien_Tay'=> '50065',        
        'Hue'=> '1283',       
        'Quoc_Phong'=> '50068',
        'Quoc_Hoi'=> '50069',
        'Thong_Tan_Xa'=> '50070',
        'HGTV'=> '762',
        'BTV2'=> '23963',
        'Binh_Duong_FM'=> '23964'
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
        $index = $this->name[$data['name']];
        
    	$post = "lang_id=2&device=android&version=1.4&id=".$index;
    	//var_dump($post);
    	$source = "http://api.hplus.com.vn/detail?lang_id=2&device=android&version=1.4&id=".$index;
    	$str=$this->curl->exec(array(
    			'url'=>$source,
    			'method'=>'post',
    			'post'=>$post
    	));
    	$url = "";
    	//var_dump($str);
    	$s = json_decode($str, true);
    	//var_dump($s['content']['link']);

    	if (isset($s['content']['link']))
    	    $url = $s['content']['link'];
    	//var_dump($url);
    	return $url;
    }       
       
    
}
