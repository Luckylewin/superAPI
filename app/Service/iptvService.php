<?php
namespace App\Service;

use App\Components\cache\Redis;
use App\Components\helper\ArrayHelper;
use App\Components\helper\Func;
use App\Exceptions\ErrorCode;
use Breeze\Helpers\Url;
use Snoopy\Snoopy;
use Illuminate\Database\Capsule\Manager as Capsule;

class iptvService extends common
{

     public function getVods()
     {
         $genres = Capsule::table('iptv_vod')->select('*')->limit(10)->get();
         if (count($genres) <= 0) {
             return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
         }

         $genres = ArrayHelper::toArray($genres);

         array_walk($genres, function(&$v) {
             $v['_links'] = [
                 'self' => [
                     Url::to('vods/' . $v['id'])
                 ],
                 'recommend' => [
                     Url::to('recommend/' . $v['id'] )
                 ],

             ];
         });

         return ['status' => true, 'data' => $genres];
     }

     public function getType()
     {
         $genres = Capsule::table('iptv_list')->select(['list_id', 'list_name', 'list_dir', 'list_ispay', 'list_price', 'list_trysee', 'list_icon'])->get();
         if (count($genres) <= 0) {
             return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
         }

         $genres = ArrayHelper::toArray($genres);

         array_walk($genres, function(&$v) {
             $v['_links'] = [
                 'self' => [
                     Url::to('types/' . $v['list_id'])
                  ],
                 'index' => [
                     Url::to('types' )
                  ],
                 'vod' => [
                     Url::to('vods' , ['cid' => $v['list_id']])
                  ],
             ];
         });

         return ['status' => true, 'data' => $genres];
     }

     public function getBanner()
     {
         $banners = Capsule::table('sys_banner')->select('*')->get();
         if (count($banners) <= 0) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
         }
         $banners = ArrayHelper::toArray($banners);

         array_walk($banners, function(&$v) {
             $v['_links'] = [
                 'self' => [Url::to('banners/' . $v['id'])],
                 'vod' => [Url::to('vods/' . $v['vod_id'])],
             ];
         });

         return ['status' => true, 'data' => $banners];
     }

     public function getKaraokeList()
     {
         try {
             $name = $this->post('name', '');
             $lang = $this->post('lang', '');
             $tags = $this->post('tags', '');
             $sort = $this->post('sort', 'update_time:asc');
         } catch (\Exception $e) {
             return ['status' => false, 'code' => $e->getCode()];
         }

         list($field, $direct) = explode(':',$sort);

         $orderMaps = [
             'update_time' =>'utime',
             'popular' => 'hit_count'
         ];

         $orderField = $orderMaps[$field];
         $searchKey = md5(serialize($this->data));

         //读缓存
         $cacheKey = "karaoke:{$searchKey}";
         $cacheDB = Redis::$REDIS_VOD_ALBUM;
         $cacheData = $this->getDataFromCache($cacheKey, $cacheDB);

         if ($cacheData) {
             return ['status' => true, 'data' => $cacheData];
         }

         $query = Capsule::table('sys_karaoke');

         if ($name) {
            $query->where('name', 'like', $name);
         }
         if ($lang) {
            $query->where('lang', '=', $lang);
         }
         if ($tags) {
            $query->where('tags', '=', $tags);
         }

         $totalItems =  $query->orderBy($orderField, $direct)
                              ->count();

         if ($totalItems == false) {
             $this->stdout("没有数据", "ERROR");
             return ['status' => false, 'data' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
         }

         //每页的数量
         $perPage = isset($data['perPage']) ? $this->data['perPage'] : 10;

         //计算总页
         $totalPage = self::getTotalPage($perPage, $totalItems);

         //计算offset
         $page = isset($data['page']) && $this->data['page'] <= $totalPage? $this->data['page'] : '1';
         $offset = ($page - 1) * $perPage;


         //取数据
         $data = $query->select(['albumName','albumImage','url','area','year','tags','yesterday_viewed'])
                       ->limit($perPage)
                       ->offset($offset)
                       ->get();

         $data = ArrayHelper::toArray($data);

         $cacheData['page'] = $page;
         $cacheData['perPage'] = $perPage;
         $cacheData['totalPage'] = $totalPage;
         $cacheData['totalItems'] = $totalItems['total'];
         $cacheData['lang'] = 'Vietnamese,Chinese,English,Korean,French,Other';
         $cacheData['data'] = $data;

         $this->getRedis()->set("karaoke:$searchKey", json_encode($cacheData));
         $this->getRedis()->expire("karaoke:$searchKey", 86400);

         return ['status' => true, 'data' => $cacheData];
     }


    /**
     * 取卡拉ok
     * @return array
     */
    public function getKaraoke()
    {
        $url = $this->post('url', null, ['string']);

        // 查询服务器
        $data = Capsule::table('sys_karaoke')
                        ->where("url", "=", $url)
                        ->first();

        if ($data->source == 'upload') {
            $url = Func::getAccessUrl($this->uid, $url, 86400);

            $karaoke['hd720'] = null;
            $karaoke['medium'] = $url;
            $karaoke['small'] = null;
            return $karaoke;
        }

        $Snnopy = new Snoopy();
        $Snnopy->scheme = 'https';
        $Snnopy->_fp_timeout = 15;
        $Snnopy->fetch('https://www.youtube.com/get_video_info?video_id='.$url);
        $videoInfo = $Snnopy->results;

        Capsule::table('sys_karaoke')
                        ->where('url','=',$url)
                        ->increment('hit_count',1);

        if (empty($videoInfo)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        parse_str($videoInfo ,$info);

        if (!isset($info['url_encoded_fmt_stream_map'])) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        $streams = explode(',',$info['url_encoded_fmt_stream_map']);
        $video = [];

        foreach($streams as $stream) {
            parse_str($stream, $real_stream);
            $video[$real_stream['quality']] = $real_stream['url'];
        }

        if (empty($video)) {
            $this->stdout("没有数据", "ERROR");
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        return ['status' => true, 'data' => $video];
    }


}