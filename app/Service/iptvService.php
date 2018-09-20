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
    /**
     * 取首页推荐
     * @return array
     */
    public function vodHome()
    {
        $query = Capsule::table('iptv_vod')->select(['vod_id', 'vod_cid', 'vod_name', 'vod_ename', 'vod_type', 'vod_actor', 'vod_director', 'vod_content', 'vod_pic', 'vod_year', 'vod_addtime', 'vod_filmtime', 'vod_ispay', 'vod_price', 'vod_trysee', 'vod_url', 'vod_gold', 'vod_length', 'vod_multiple']);
        $vods = $query->where('vod_home', '=', '1')->get();

        if ($vods->count() <= 0) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        $vods = ArrayHelper::toArray($vods);

        array_walk($vods, function(&$v) {
            $v['_links'] = [
                'self' => [Url::to('vods/' . $v['vod_id'], ['expand' => 'vodLinks'])],
                'recommend' => [Url::to('recommend/' . $v['vod_id'])]
            ];
        });

        return ['status' => true, 'data' => $vods];
    }

    /**
     * 获取片源推荐
     * @param $id
     * @return array
     */
    public function getRecommends($id)
    {
        $num = $this->request->get('num');
        $num = is_null($num) ? 4 : $num;
        $num = $num > 12 ? 12 : $num;

        $vod = Capsule::table('iptv_vod')->select(['vod_id', 'vod_type', 'vod_name'])->where('vod_id', '=', $id)->first();
        if (is_null($vod)) {
           return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        if (strpos($vod->vod_type, ',') ) {
            $types = explode(',', $vod->vod_type);
            $total = count($types);
            $type = $types[mt_rand(0, $total - 1)];
        } else {
            $type = $vod->vod_type;
        }

        $query = Capsule::table('iptv_vod')->select(['vod_id', 'vod_cid', 'vod_name', 'vod_ename', 'vod_type', 'vod_actor', 'vod_director', 'vod_content', 'vod_pic', 'vod_year', 'vod_addtime', 'vod_filmtime', 'vod_ispay', 'vod_price', 'vod_trysee', 'vod_url', 'vod_gold', 'vod_length', 'vod_multiple']);
        $vods = $query->where([
            ['vod_id' ,'<>', $vod->vod_id ],
            ['vod_type', 'like', "%{$type}%"]
        ])->limit($num)->get();

        if ($vods->count() <= 0) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        $vods = ArrayHelper::toArray($vods);

        array_walk($vods, function(&$v) {
            $v['_links'] = [
                'self' => [Url::to('vods/' . $v['vod_id'], ['expand' => 'vodLinks'])],
                'recommend' => [Url::to('recommend/' . $v['vod_id'])]
            ];
        });

        return ['status' => true, 'data' => $vods];
    }

    /**
     * 获取片链接
     * @return array
     */
    public function getVodLinks()
    {
        $vod_id = $this->request->get('vod_id');

        if (empty($vod_id)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_PARAMETER_MISSING];
        }

        $links = Capsule::table('iptv_vodlink')
                        ->select(['id', 'episode', 'plot'])
                        ->where('video_id', '=', $vod_id)
                        ->get();

        if ($links->count() <= 0) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        $links = ArrayHelper::toArray($links);

        return ['status' => false, 'data' => $links];
    }

    /**
     * 获取影片详情
     * @param $id
     * @return array
     */
    public function getVod($id)
    {
        $vod = Capsule::table('iptv_vod')
                    ->select('*')
                    ->where('vod_id', '=', $id)
                    ->first();

        if (is_null($vod)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        $vod = ArrayHelper::toArray($vod);

        $expand = $this->request->get('expand');

        if ($expand == 'vodLinks') {
            $query = Capsule::table('iptv_vodlink')
                        ->select(['id', 'episode', 'plot'])
                        ->where('video_id', '=', $id);
            $total = $query->count();

            if ($total) {
                $links = ArrayHelper::toArray($query->get());
                array_walk($links, function(&$v) {
                    $v['_links']['self']['href'] = Url::to("vod-links/{$v['id']}", ['access-token' => '']);
                });

                $vod['vodLinks'] = [
                    'total' => $total,
                    'links' => $links
                ];
            }
        }

        $vod['_links'] = [
            'self' => ['href' => Url::to("vods/{$id}", ["expand" => "vodLinks"])],
            'recommend' => ['href' => Url::to("recommends/{$id}")]
        ];

        return ['status' => true, 'data' => $vod];
    }

    /**
     * 获取影片列表
     * @return array
     */
     public function getVods()
     {
         $cid = $this->request->get('cid') ?? '';
         $name = $this->request->get('name') ?? '';
         $type = $this->request->get('type') ?? '';
         $year = $this->request->get('year') ?? '';
         $area = $this->request->get('area') ?? '';

         $per_page = $this->request->get('per_page') ?? 12;
         $page = $this->request->get('page') ?? 1;


         $query = Capsule::table('iptv_vod')->select(['vod_id', 'vod_cid', 'vod_name', 'vod_ename', 'vod_type', 'vod_actor', 'vod_director', 'vod_content', 'vod_pic', 'vod_year', 'vod_addtime', 'vod_filmtime', 'vod_ispay', 'vod_price', 'vod_trysee', 'vod_url', 'vod_gold', 'vod_length', 'vod_multiple']);

         // 分类ID
         if ($cid) {
             $query->where('vod_cid', '=', $cid);
         }

         // 片名
         if ($name) {
             $query->where('vod_name', 'like', '%' . $name . '%');
         }

         // 年份
         if ($year) {
            $query->where('vod_year', '=', $year);
         }

         // 类型
         if ($type) {
            $query->where('vod_type', 'like', '%' . $type . '%');
         }

         // 地区
         if ($area) {
             $query->where('vod_area', 'like', '%' . $area . '%');
         }

         $totalCount = $query->count();
         $pageCount = ceil($totalCount / $per_page);

         if ($page > $pageCount) {
             $page = $pageCount;
         }

         $offset = ($page - 1) * $per_page;

         // 参数检查
         $params = [
                    'cid' => $cid,
                    'name' => $name,
                    'per_page' => $per_page,
                    'page' => $page,

         ];

         foreach ($params as $key => $param) {
             if (empty($param)) {
                 unset($params[$key]);
             }
         }

         $vods = $query->offset($offset)->limit($per_page)->get();

         if (count($vods) <= 0) {
             return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
         }

         $vods = ArrayHelper::toArray($vods);
         array_walk($vods, function(&$v) {
             $v['_links'] = [
                 'self' => [Url::to('vods/' . $v['vod_id'], ['expand' => 'vodLinks'])],
                 'recommend' => [Url::to('recommend/' . $v['vod_id'])]
             ];
         });

         $data['items'] = $vods;

         $data['_meta'] = [
             'totalCount' => $totalCount,
             'pageCount' => $pageCount,
             'currentPage' => $page,
             'perPage' => $per_page
         ];

         $data['_links'] = $this->setLinks($data['_meta'], $params);

         return ['status' => true, 'data' => $data];
     }

    /**
     * 设置资源描述
     * @param $meta
     * @param $params
     * @return array
     */
     private function setLinks($meta, $params)
     {
         if ($params['page'] == 1) {
             if ($meta['totalCount'] == 1) {
                 return [
                     'self' => $this->setSelf($params)
                 ];
             } else {
                 return [
                     'self' => $this->setSelf($params),
                     'next' => $this->setNext($params),
                     'last' => $this->setLast($params, $meta)
                 ];
             }
         } else if ($params['page'] < $meta['totalCount']) {
             return [
                 'self' => $this->setSelf($params),
                 'first' => $this->setFirst($params),
                 'prev' => $this->setPrev($params),
                 'next' => $this->setNext($params),
                 'last' => $this->setLast($params, $meta)
             ];
         } else {
             return [
                 'self' => $this->setSelf($params),
                 'first' => $this->setFirst($params),
                 'prev' => $this->setPrev($params),
             ];
         }
     }

     private function setSelf($params)
     {
         return ['href' => Url::to('vods', $params)];
     }

     private function setFirst($params)
     {
         $params['page'] = 1;
         return ['href' => Url::to('vods', $params)];
     }

     private function setPrev($params)
     {
        $params['page']--;
        return ['href' => Url::to('vods', $params)];
     }

     private function setNext($params)
     {
         $params['page']++;
         return ['href' => Url::to('vods', $params)];
     }

     private function setLast($params, $meta)
     {
        $params['page'] = $meta['pageCount'];
        return ['href' => Url::to('vods', $params)];
     }

    /**
     * 获取影片类型
     * @return array
     */
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

    /**
     * 获取banner图
     * @return array
     */
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


    public function getCondition()
    {
        $vod_id = $this->request->get('vod_id');

        if (empty($vod_id)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_PARAMETER];
        }

        $items = Capsule::table('iptv_type AS a')
                        ->where('vod_list_id', '=', '1')
                        ->select(['a.*', 'b.name as itemName','b.zh_name'])
                        ->orderBy('a.sort')
                        ->leftJoin('iptv_type_item AS b', 'b.type_id', '=', 'a.id')
                        ->get()
                        ->toArray();



        $data = [];
        foreach ($items as $item) {
            $data[$item->field]['name'] = $item->name;
            $data[$item->field]['field'] = $item->field;
            $data[$item->field]['items'][] = ['name' => $item->itemName, 'zh_name' => $item->zh_name];
        }

        return ['status' => true, 'data' => array_values($data)];
    }

    /**
     * 获取卡拉OK列表
     * @return array
     */
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
     * @throws \Exception
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


    public function getLink($id)
    {
        if (empty($id)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_PARAMETER_MISSING];
        }

        $link = Capsule::table('iptv_vodlink')->where('id', '=', $id)->first();

        if (is_null($link)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        $link = ArrayHelper::toArray($link);

        return ['status' => true, 'data' => $link];
    }

}