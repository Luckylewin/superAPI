<?php
/**
 * Created by PhpStorm.
 * User: sz
 * Date: 2017/8/19
 * Time: 11:48
 */
namespace App\Service\ott;


class vtv extends ottbase
{
    public $key = array('name','cdn');
    public $expireTime = 3600;
    public $debug = true;
    public $retry = 0;
    public static $refreshTotal = 0;

    public $typeToname = '[{"name":"vtv11","type":"vtv"},{"name":"vtv12","type":"vtv"},{"name":"vtv13","type":"vtv"},{"name":"vtv14","type":"vtv"},{"name":"vtv15","type":"vtv"},{"name":"vtv16","type":"vtv"},{"name":"vtv21","type":"vtv"},{"name":"vtv22","type":"vtv"},{"name":"vtv23","type":"vtv"},{"name":"vtv24","type":"vtv"},{"name":"vtv25","type":"vtv"},{"name":"vtv3_1","type":"vtv"},{"name":"vtv3_2","type":"vtv"},{"name":"vtv3_3","type":"vtv"},{"name":"vtv3_4","type":"vtv"},{"name":"vtv3_5","type":"vtv"},{"name":"vtv3_6","type":"vtv"},{"name":"vtv3_7","type":"vtv"},{"name":"vtv3_1","type":"vtv"},{"name":"vtv3_2","type":"vtv"},{"name":"vtv3_3","type":"vtv"},{"name":"vtv3_4","type":"vtv"},{"name":"vtv3_5","type":"vtv"},{"name":"vtv3_6","type":"vtv"},{"name":"vtv3_7","type":"vtv"},{"name":"vtv6_1","type":"vtv"},{"name":"vtv6_2","type":"vtv"},{"name":"vtv6_3","type":"vtv"},{"name":"vtv6_4","type":"vtv"},{"name":"vtv6_5","type":"vtv"},{"name":"antv","type":"vtv"},{"name":"antv1","type":"vtv"},{"name":"antv2","type":"vtv"},{"name":"vtc7_1","type":"vtc"},{"name":"vtc7_2","type":"vtc"},{"name":"vtc7_3","type":"vtc"},{"name":"vtc7_4","type":"vtc"},{"name":"vtc7_5","type":"vtc"},{"name":"itv_1","type":"vtc"},{"name":"itv_2","type":"vtc"},{"name":"itv_3","type":"vtc"},{"name":"youtv_1","type":"vtc"},{"name":"youtv_2","type":"vtc"},{"name":"htv7_1","type":"htv"},{"name":"htv7_2","type":"htv"},{"name":"htv7_3","type":"htv"},{"name":"htv7_4","type":"htv"},{"name":"htv9_1","type":"htv"},{"name":"htv9_2","type":"htv"},{"name":"htv9_3","type":"htv"},{"name":"htv9_4","type":"htv"},{"name":"htvthethao_1","type":"htv"},{"name":"htvthethao_2","type":"htv"},{"name":"htvthethao_3","type":"htv"},{"name":"vinhlong1_1","type":"sctv"},{"name":"vinhlong1_2","type":"sctv"},{"name":"vinhlong1_3","type":"sctv"},{"name":"vinhlong1_4","type":"sctv"},{"name":"vinhlong1_5","type":"sctv"},{"name":"vinhlong2_1","type":"sctv"},{"name":"vinhlong2_2","type":"sctv"},{"name":"infotv_1","type":"vtv"},{"name":"infotv_2","type":"vtv"},{"name":"bibi_1","type":"vtv"},{"name":"bibi_2","type":"vtv"},{"name":"hn1_1","type":"diaphuong"},{"name":"hn1_2","type":"diaphuong"},{"name":"hn1_3","type":"diaphuong"},{"name":"hn1_4","type":"diaphuong"},{"name":"hn2_1","type":"diaphuong"},{"name":"hn2_2","type":"diaphuong"},{"name":"hn2_3","type":"diaphuong"},{"name":"hbo_1","type":"nuocngoai"},{"name":"hbo_2","type":"nuocngoai"},{"name":"starmovie_1","type":"nuocngoai"},{"name":"starmovie_2","type":"nuocngoai"},{"name":"starmovie_3","type":"nuocngoai"},{"name":"axn_1","type":"nuocngoai"},{"name":"axn_2","type":"nuocngoai"},{"name":"axn_3","type":"nuocngoai"},{"name":"discovery_1","type":"nuocngoai"},{"name":"discovery_2","type":"nuocngoai"},{"name":"discovery_3","type":"nuocngoai"},{"name":"cartoon_1","type":"nuocngoai"},{"name":"cartoon_2","type":"nuocngoai"},{"name":"cartoon_3","type":"nuocngoai"},{"name":"fashion_1","type":"nuocngoai"},{"name":"cinemax_1","type":"nuocngoai"},{"name":"cinemax_2","type":"nuocngoai"},{"name":"cinemax_3","type":"nuocngoai"},{"name":"vtc3hd_1","type":"vtc"},{"name":"vtc3hd_2","type":"vtc"},{"name":"thethaotv","type":"vtv"},{"name":"thethaotv_2","type":"vtv"},{"name":"sctv2_1","type":"sctv"},{"name":"sctv2_2","type":"sctv"},{"name":"sctv2_3","type":"sctv"},{"name":"disney_1","type":"nuocngoai"},{"name":"disney_2","type":"nuocngoai"},{"name":"disney_3","type":"nuocngoai"},{"name":"phimhay_1","type":"khac"},{"name":"phimhay_2","type":"khac"},{"name":"vtv11","type":"vtv"},{"name":"vtv12","type":"vtv"},{"name":"vtv13","type":"vtv"},{"name":"vtv14","type":"vtv"},{"name":"vtv15","type":"vtv"},{"name":"vtv16","type":"vtv"},{"name":"vtv21","type":"vtv"},{"name":"vtv22","type":"vtv"},{"name":"vtv23","type":"vtv"},{"name":"vtv24","type":"vtv"},{"name":"vtv25","type":"vtv"},{"name":"vtv3_1","type":"vtv"},{"name":"vtv3_2","type":"vtv"},{"name":"vtv3_3","type":"vtv"},{"name":"vtv3_4","type":"vtv"},{"name":"vtv3_5","type":"vtv"},{"name":"vtv3_6","type":"vtv"},{"name":"vtv3_7","type":"vtv"},{"name":"vtv4_1","type":"vtv"},{"name":"vtv4_2","type":"vtv"},{"name":"vtv5_1","type":"vtv"},{"name":"vtv5_2","type":"vtv"},{"name":"vtv6_1","type":"vtv"},{"name":"vtv6_2","type":"vtv"},{"name":"vtv6_3","type":"vtv"},{"name":"vtv6_4","type":"vtv"},{"name":"vtv6_5","type":"vtv"},{"name":"vtv7_1","type":"vtv"},{"name":"vtv7_2","type":"vtv"},{"name":"vtv8_1","type":"vtv"},{"name":"vtv8_2","type":"vtv"},{"name":"vtv8_3","type":"vtv"},{"name":"vtv8_1","type":"vtv"},{"name":"vtv8_2","type":"vtv"},{"name":"vtv8_3","type":"vtv"},{"name":"vtvcantho_1","type":"vtv"},{"name":"vtvcantho_2","type":"vtv"},{"name":"antv","type":"vtv"},{"name":"antv1","type":"vtv"},{"name":"antv2","type":"vtv"},{"name":"vovtv_1","type":"diaphuong"},{"name":"vovtv_2","type":"diaphuong"},{"name":"phimviet_1","type":"vtv"},{"name":"phimviet_2","type":"vtv"},{"name":"giaitritv_1","type":"vtv"},{"name":"giaitritv_2","type":"vtv"},{"name":"echannel_1","type":"vtv"},{"name":"echannel_2","type":"vtv"},{"name":"haytv_1","type":"vtv"},{"name":"haytv_2","type":"vtv"},{"name":"infotv_1","type":"vtv"},{"name":"infotv_2","type":"vtv"},{"name":"investtv_1","type":"vtv"},{"name":"investtv_2","type":"vtv"},{"name":"vctv7_1","type":"vtv"},{"name":"vctv7_2","type":"vtv"},{"name":"bibi_1","type":"vtv"},{"name":"bibi_2","type":"vtv"},{"name":"vctv10_1","type":"vtv"},{"name":"vctv10_2","type":"vtv"},{"name":"vctv12_1","type":"vtv"},{"name":"vctv12_2","type":"vtv"},{"name":"vctv12_3","type":"vtv"},{"name":"vctv17_1","type":"vtv"},{"name":"vctv17_2","type":"vtv"},{"name":"vtc1_1","type":"vtc"},{"name":"vtc1_2","type":"vtc"},{"name":"vtc1_3","type":"vtc"},{"name":"vtc2_1","type":"vtc"},{"name":"vtc2_2","type":"vtc"},{"name":"vtc2_3","type":"vtc"},{"name":"vtc3_1","type":"vtc"},{"name":"vtc3_2","type":"vtc"},{"name":"vtc3_3","type":"vtc"},{"name":"vtc4_1","type":"vtc"},{"name":"vtc4_2","type":"vtc"},{"name":"vtc4_2","type":"vtc"},{"name":"vtc5_1","type":"vtc"},{"name":"vtc5_2","type":"vtc"},{"name":"vtc5_3","type":"vtc"},{"name":"referrer","type":"vtc"},{"name":"vtc7_1","type":"vtc"},{"name":"vtc7_2","type":"vtc"},{"name":"vtc7_3","type":"vtc"},{"name":"vtc7_4","type":"vtc"},{"name":"vtc7_5","type":"vtc"},{"name":"vtc9_1","type":"vtc"},{"name":"vtc9_2","type":"vtc"},{"name":"vtc9_3","type":"vtc"},{"name":"vtc9_4","type":"vtc"},{"name":"vtc10_1","type":"vtc"},{"name":"vtc10_2","type":"vtc"},{"name":"vtc10_3","type":"vtc"},{"name":"vtc11_1","type":"vtc"},{"name":"vtc11_2","type":"vtc"},{"name":"vtc11_3","type":"vtc"},{"name":"vtc12_1","type":"vtc"},{"name":"vtc12_2","type":"vtc"},{"name":"vtc14_1","type":"vtc"},{"name":"vtc14_2","type":"vtc"},{"name":"referrer","type":"vtc"},{"name":"itv_1","type":"vtc"},{"name":"itv_2","type":"vtc"},{"name":"itv_3","type":"vtc"},{"name":"htv1_1","type":"htv"},{"name":"htv1_2","type":"htv"},{"name":"htv1_3","type":"htv"},{"name":"htv2_1","type":"htv"},{"name":"htv2_2","type":"htv"},{"name":"htv2_3","type":"htv"},{"name":"htv3_1","type":"htv"},{"name":"htv3_2","type":"htv"},{"name":"htv3_3","type":"htv"},{"name":"htv7_1","type":"htv"},{"name":"htv7_2","type":"htv"},{"name":"htv7_3","type":"htv"},{"name":"htv7_4","type":"htv"},{"name":"htv9_1","type":"htv"},{"name":"htv9_2","type":"htv"},{"name":"htv9_3","type":"htv"},{"name":"htv9_4","type":"htv"},{"name":"htvthethao_1","type":"htv"},{"name":"htvthethao_2","type":"htv"},{"name":"htvthethao_3","type":"htv"},{"name":"htvphim_1","type":"htv"},{"name":"htvphim_2","type":"htv"},{"name":"htvphim_3","type":"htv"},{"name":"htvgiadinh_1","type":"htv"},{"name":"htvgiadinh_2","type":"htv"},{"name":"htvphunu_1","type":"htv"},{"name":"htvphunu_2","type":"htv"},{"name":"htvphunu_3","type":"htv"},{"name":"htvdulich_1","type":"htv"},{"name":"htvdulich_2","type":"htv"},{"name":"htvdulich_3","type":"htv"},{"name":"htvthuanviet_1","type":"htv"},{"name":"htvthuanviet_2","type":"htv"},{"name":"htvthuanviet_3","type":"htv"},{"name":"htvcanhac_1","type":"htv"},{"name":"htvcanhac_2","type":"htv"},{"name":"yeah1tv_1","type":"htv"},{"name":"yeah1tv_2","type":"htv"},{"name":"sctv2_1","type":"sctv"},{"name":"sctv2_2","type":"sctv"},{"name":"sctv2_3","type":"sctv"},{"name":"vinhlong1_1","type":"sctv"},{"name":"vinhlong1_2","type":"sctv"},{"name":"vinhlong1_3","type":"sctv"},{"name":"vinhlong1_4","type":"sctv"},{"name":"vinhlong1_5","type":"sctv"},{"name":"vinhlong2_1","type":"sctv"},{"name":"vinhlong2_2","type":"sctv"},{"name":"hn1_1","type":"diaphuong"},{"name":"hn1_2","type":"diaphuong"},{"name":"hn1_3","type":"diaphuong"},{"name":"hn1_4","type":"diaphuong"},{"name":"hn2_1","type":"diaphuong"},{"name":"hn2_2","type":"diaphuong"},{"name":"hn2_3","type":"diaphuong"},{"name":"ntv_1","type":"diaphuong"},{"name":"ntv_2","type":"diaphuong"},{"name":"hbo_1","type":"nuocngoai"},{"name":"hbo_2","type":"nuocngoai"},{"name":"starmovie_1","type":"nuocngoai"},{"name":"starmovie_2","type":"nuocngoai"},{"name":"starmovie_3","type":"nuocngoai"},{"name":"starworld_1","type":"nuocngoai"},{"name":"starworld_2","type":"nuocngoai"},{"name":"starworld_3","type":"nuocngoai"},{"name":"axn_1","type":"nuocngoai"},{"name":"axn_2","type":"nuocngoai"},{"name":"axn_3","type":"nuocngoai"},{"name":"discovery_1","type":"nuocngoai"},{"name":"discovery_2","type":"nuocngoai"},{"name":"discovery_3","type":"nuocngoai"},{"name":"foxsport1_1","type":"nuocngoai"},{"name":"foxsport1_2","type":"nuocngoai"},{"name":"foxsport2_1","type":"nuocngoai"},{"name":"foxsport2_2","type":"nuocngoai"},{"name":"mtv_1","type":"nuocngoai"},{"name":"mtv_2","type":"nuocngoai"},{"name":"referrer","type":"nuocngoai"},{"name":"cartoon_1","type":"nuocngoai"},{"name":"cartoon_2","type":"nuocngoai"},{"name":"cartoon_3","type":"nuocngoai"},{"name":"referrer","type":"nuocngoai"},{"name":"cnn_1","type":"nuocngoai"},{"name":"cnn_2","type":"nuocngoai"},{"name":"fashion_1","type":"nuocngoai"},{"name":"cinemax_1","type":"nuocngoai"},{"name":"cinemax_2","type":"nuocngoai"},{"name":"cinemax_3","type":"nuocngoai"},{"name":"disney_1","type":"nuocngoai"},{"name":"disney_2","type":"nuocngoai"},{"name":"disney_3","type":"nuocngoai"},{"name":"bloomberg_1","type":"nuocngoai"},{"name":"bloomberg_2","type":"nuocngoai"},{"name":"bloomberg_3","type":"nuocngoai"},{"name":"referrer","type":"nuocngoai"},{"name":"vtc3hd_1","type":"vtc"},{"name":"vtc3hd_2","type":"vtc"},{"name":"thethaotv","type":"vtv"},{"name":"thethaotv_2","type":"vtv"},{"name":"htvthethao_1","type":"htv"},{"name":"htvthethao_2","type":"htv"},{"name":"htvthethao_3","type":"htv"},{"name":"starsport_1","type":"nuocngoai"},{"name":"starsport_2","type":"nuocngoai"},{"name":"foxsport1_1","type":"nuocngoai"},{"name":"foxsport1_2","type":"nuocngoai"},{"name":"foxsport2_1","type":"nuocngoai"},{"name":"foxsport2_2","type":"nuocngoai"},{"name":"cctv5_1","type":"bongda"},{"name":"cctv5_2","type":"bongda"},{"name":"iframe","type":"khac"},{"name":"vitv_1","type":"khac"},{"name":"vitv_2","type":"khac"},{"name":"qpvn_1","type":"khac"},{"name":"qpvn_2","type":"khac"}]';
    /**
     * vtv constructor.
     */
    public function __construct()
    {
        $this->typeToname = json_decode($this->typeToname,true);
    }


    public function getKey()
    {
        return $this->key;
    }

    public function getExpireTime()
    {
        return $this->expireTime;
    }

    /**
     * 获取类型
     * @param $name
     * @param $cdn
     * @return mixed
     */
    public function getType($name,$cdn)
    {   $pattern = preg_replace('/(\d+_)?\d+/','',$cdn);
        foreach ($this->typeToname as $type) {
            if ($type['name'] == $cdn) {
                return $type['type'];
            }
        }
        foreach ($this->typeToname as $type) {
            if (isset($type['name']) && preg_match("/{$pattern}/",$type['name'])) {
                return $type['type'];
            }
        }
        if (preg_match('/htv/',$name))  return 'htv';
        if (preg_match('/vtc/',$name))  return 'vtc';
        if (preg_match('/sctv/',$name)) return 'sctv';

        return str_replace('hd','',preg_replace('/\d+/','',$name));
    }

    public function getUrl($data)
    {
        echo "vtv" . $data['name'] . "\n";
        $name = $this->getType($data['name'],$data['cdn']);
        $href = "http://tv.101vn.com/ok/$name/" . $data['cdn'] . ".php";
        $referer = preg_replace('/_?\d+/','show.php',$data['cdn']);
        $data = $this->curl($href,$referer);
        if ($data) {
            preg_match('/(?<=iframe src=")[^"]+(?=")/',$data,$url);
            if (!empty($url)) {
                $url = $url[0];
                $data = $this->curl($url,$href);
                if ($data) {
                    preg_match_all("/(?<=play\(')[^)]+(?='\))/",$data,$finalMatch);
                    if (isset($finalMatch) && !empty($finalMatch[0])) {
                        return $finalMatch[0][0];
                    }
                    //再次匹配iframe
                    elseif (preg_match('/(?<=iframe src=\')[^\']+(?=\')/',$data,$url)) {
                        $link =  $url[0];
                        $data = $this->curl($link,$href);
                        if ($data) {
                            preg_match('/[^\'"]+m3u8/',$data,$link);
                            return isset($link[0])?$link[0]:false;
                        }
                        echo "该链接不可用\n";
                    //匹配跳转
                    }elseif(preg_match('/(?<=iframe src=")[^"]+(?=")/',$data,$url)){
                        $link =  $url[0];
                        if (preg_match('/(?<=null.php\?url=)[^"\']+/',$link,$refresh)) {
                            if (!empty($refresh) && self::$refreshTotal <= 3) {
                                echo "重定向跳转\n";
                                self::$refreshTotal++;
                                $data = $this->analyze($refresh[0]);
                                return $this->getUrl($data);
                            }
                        }
                        return $link;
                    }
                }
            } else {

                //匹配src3
                preg_match('/(?<=src3 \= ")[^"]+/',$data,$finalMatch);
                echo "匹配src3 ",PHP_EOL;
                if (isset($finalMatch[0]) && !empty($finalMatch[0])) {
                    print_r($finalMatch);
                    if ($this->testPlay($finalMatch[0])) {
                        return $finalMatch[0];
                    }
                }
                //匹配play
                preg_match_all("/(?<=play\(')http[^']+/",$data,$finalMatch);
                echo "匹配play",PHP_EOL;
                if (isset($finalMatch[0]) && !empty($finalMatch[0])) {
                    print_r($finalMatch);
                    if ($this->testPlay(end($finalMatch[0]))) return end($finalMatch[0]);
                    if ($this->testPlay($finalMatch[0][0])) return $finalMatch[0][0];
                    return false;
                }

                //匹配adPlayer
                preg_match("/(?<=addPlayer\(')[^']+/",$data,$finalMatch);
                echo "匹配adPlayer",PHP_EOL;
                if (isset($finalMatch[0]) && !empty($finalMatch[0])) {
                    print_r($finalMatch);
                    return $finalMatch[0];
                }
                //匹配file
                preg_match("/(?<=file:\ ')[^']+/",$data,$finalMatch);
                echo "匹配file" , PHP_EOL;
                if (isset($finalMatch[0]) && !empty($finalMatch[0])) {
                    return $finalMatch[0];
                }
                echo "iframe and play and addPlayer为空\n";
            }

        } else {
            echo "data为空\n";
        }


    }

    /**
     * 简单测试是否能播放
     * @param $url
     * @return bool
     */
    private function testPlay($url)
    {
        $response = trim(file_get_contents($url,false,null,0,100));
        if (!empty($response) && preg_match('/#/',$response)) {
            return true;
        }
        return false;
    }

    /**
     * 获取重定向的内容
     * @param $url
     * @return mixed
     */
    private function analyze($url)
    {
        $url = explode('/',$url);
        $data['cdn'] = str_replace('.php','',$url[count($url)-1]);
        $data['name'] = $url[count($url)-2];
        return $data;
    }

    /**
     * curl
     * @param $url string 要访问的url
     * @param $referer string 指定referer头部
     * @return bool|mixed
     */
    public function curl($url,$referer)
    {
        $ch =curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        $header = array();
        curl_setopt ($ch, CURLOPT_REFERER, $referer);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_HEADER,true);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$header);

        $content = curl_exec($ch);
        if ($this->debug) {
            print_r(curl_getinfo($ch));
        }
        if (curl_error($ch)) {
            echo "curl出错\n";
            //var_dump(curl_error($ch));
            return false;
        }
        return $content;
    }


}