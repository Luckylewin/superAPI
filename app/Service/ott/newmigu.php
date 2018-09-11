<?php
/**
 * Created by PhpStorm.
 * User: sz
 * Date: 2017/8/2
 * Time: 11:26
 */


namespace App\Service\ott;

use Snoopy\Snoopy;

class newmigu extends ottbase
{
     public $key = array('name','cdn');
     public $expireTime = 3600;

     public $str = 'a:30:{s:4:"hnws";a:2:{s:5:"title";s:12:"湖南卫视";s:2:"id";a:6:{i:0;s:4:"6418";i:1;s:4:"6582";i:2;s:4:"6583";i:3;s:4:"6579";i:4;s:4:"6671";i:5;s:4:"6367";}}s:4:"jsws";a:2:{s:5:"title";s:12:"江苏卫视";s:2:"id";a:1:{i:0;s:4:"6782";}}s:4:"ahws";a:2:{s:5:"title";s:12:"安徽卫视";s:2:"id";a:4:{i:0;s:4:"6025";i:1;s:4:"6024";i:2;s:4:"6023";i:3;s:4:"6020";}}s:4:"ssws";a:2:{s:5:"title";s:12:"三沙卫视";s:2:"id";a:1:{i:0;s:4:"2775";}}s:4:"btws";a:2:{s:5:"title";s:12:"兵团卫视";s:2:"id";a:3:{i:0;s:4:"6422";i:1;s:4:"2885";i:2;s:4:"6794";}}s:4:"qhws";a:2:{s:5:"title";s:12:"青海卫视";s:2:"id";a:2:{i:0;s:4:"6768";i:1;s:4:"6767";}}s:4:"zqws";a:2:{s:5:"title";s:12:"重庆卫视";s:2:"id";a:6:{i:0;s:4:"6048";i:1;s:4:"6754";i:2;s:4:"6753";i:3;s:4:"6045";i:4;s:4:"6752";i:5;s:4:"6751";}}s:4:"gzws";a:2:{s:5:"title";s:12:"贵州卫视";s:2:"id";a:3:{i:0;s:4:"5464";i:1;s:4:"3190";i:2;s:4:"3055";}}s:4:"lyws";a:2:{s:5:"title";s:12:"旅游卫视";s:2:"id";a:3:{i:0;s:4:"6423";i:1;s:4:"6282";i:2;s:4:"5458";}}s:4:"gsws";a:2:{s:5:"title";s:12:"甘肃卫视";s:2:"id";a:2:{i:0;s:4:"6766";i:1;s:4:"6765";}}s:4:"xjws";a:2:{s:5:"title";s:12:"新疆卫视";s:2:"id";a:2:{i:0;s:4:"6370";i:1;s:4:"2872";}}s:4:"xcws";a:2:{s:5:"title";s:12:"西藏卫视";s:2:"id";a:2:{i:0;s:4:"6797";i:1;s:4:"6796";}}s:4:"nxws";a:2:{s:5:"title";s:12:"宁夏卫视";s:2:"id";a:2:{i:0;s:4:"6764";i:1;s:4:"6763";}}s:5:"nmgws";a:2:{s:5:"title";s:15:"内蒙古卫视";s:2:"id";a:2:{i:0;s:4:"6760";i:1;s:4:"6759";}}s:5:"ssxws";a:2:{s:5:"title";s:15:"陕陕西卫视";s:2:"id";a:3:{i:0;s:4:"2715";i:1;s:4:"6770";i:2;s:4:"6769";}}s:4:"sxws";a:2:{s:5:"title";s:12:"山西卫视";s:2:"id";a:2:{i:0;s:4:"6417";i:1;s:4:"6773";}}s:4:"gxws";a:2:{s:5:"title";s:12:"广西卫视";s:2:"id";a:1:{i:0;s:4:"6419";}}s:4:"jlws";a:2:{s:5:"title";s:12:"吉林卫视";s:2:"id";a:3:{i:0;s:4:"5344";i:1;s:4:"6756";i:2;s:4:"6755";}}s:4:"hbws";a:2:{s:5:"title";s:12:"河北卫视";s:2:"id";a:5:{i:0;s:4:"6775";i:1;s:4:"5341";i:2;s:4:"6772";i:3;s:4:"3195";i:4;s:4:"6771";}}s:4:"xmws";a:2:{s:5:"title";s:12:"厦门卫视";s:2:"id";a:1:{i:0;s:4:"6785";}}s:4:"dnws";a:2:{s:5:"title";s:12:"东南卫视";s:2:"id";a:1:{i:0;s:4:"6783";}}s:3:"sws";a:2:{s:5:"title";s:12:"深圳卫视";s:2:"id";a:3:{i:0;s:4:"6789";i:1;s:4:"6788";i:2;s:4:"6246";}}s:4:"gdws";a:2:{s:5:"title";s:12:"广东卫视";s:2:"id";a:1:{i:0;s:4:"6787";}}s:4:"scws";a:2:{s:5:"title";s:12:"四川卫视";s:2:"id";a:4:{i:0;s:4:"6421";i:1;s:4:"6285";i:2;s:4:"6793";i:3;s:4:"6792";}}s:4:"ynws";a:2:{s:5:"title";s:12:"云南卫视";s:2:"id";a:2:{i:0;s:4:"6791";i:1;s:4:"6790";}}s:4:"sdws";a:2:{s:5:"title";s:12:"山东卫视";s:2:"id";a:2:{i:0;s:4:"6244";i:1;s:4:"6245";}}s:5:"hljws";a:2:{s:5:"title";s:15:"黑龙江卫视";s:2:"id";a:1:{i:0;s:4:"6247";}}s:4:"jxws";a:2:{s:5:"title";s:12:"江西卫视";s:2:"id";a:2:{i:0;s:4:"6780";i:1;s:4:"6779";}}s:4:"tjws";a:2:{s:5:"title";s:12:"天津卫视";s:2:"id";a:1:{i:0;s:4:"6270";}}s:4:"bjws";a:2:{s:5:"title";s:12:"北京卫视";s:2:"id";a:3:{i:0;s:4:"6748";i:1;s:4:"6747";i:2;s:4:"6746";}}}';
     public $list;

     public function __construct()
     {
         parent::__construct();
         $this->list = unserialize($this->str);
     }

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
         echo "newmigu:{$data['name']}";
         $channelName = $data['name'];
         $chanelID = $data['cdn'] ;
         if (isset($this->list[$channelName])) {
             $id = $this->list[$channelName]['id'];
             if (!in_array($chanelID,$id)) {
                echo "没有这个频道id :( \n";return false;
             }
             $id = $chanelID;
             echo "id={$id}\n";
             return $this->_Snnopy($id);
         }
         echo "没有这个台 :(\n";
         return false;
     }

     public function _Snnopy($id)
     {
         $snnopy = new Snoopy();
         $snnopy->agent = "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.86 Safari/537.36";
         $snnopy->referer = "http://www.haoqu.net";
         $snnopy->fetch("http://www.haoqu.net/e/extend/tv.php?id={$id}");
         $data = iconv("gbk","utf-8",$snnopy->results);
         preg_match("/signal\s+=\s+\S+/",$data,$match);
         if (isset($match[0])) {
             $match = explode("$",$match[0]);
             $url = $match[1];
             if (preg_match('/player.haoqu.net/',$url)) {
                echo "站外直播 没有播放源\n";
                return false;
             }
             if(preg_match('/rtmp|m3u8/',$url)) {
                return $url;
             }
//             elseif (isset($match[1]) && strpos($match[1],'http') !== false) {
//
//                 $snnopy->fetch($url);
//                 $m3u8 = $snnopy->results;
//                 preg_match("/encodeURIComponent.*/",$m3u8,$match);
//                 if (isset($match[0]) && strpos($match[0],'http') !== false) {
//                     $m3u8Link = $match[0];
//                     return $m3u8Link = str_replace(array("encodeURIComponent('","'),"),array("",""),$m3u8Link);
//                 }
//                 echo "没有获取到播放地址";
//             }
         }
         echo "没有匹配到signal";
         return false;
     }


}