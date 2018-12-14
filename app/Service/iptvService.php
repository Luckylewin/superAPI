<?php
namespace App\Service;

use App\Components\cache\Redis;
use App\Components\helper\ArrayHelper;
use App\Components\helper\Func;
use App\Exceptions\ErrorCode;
use App\Models\KaraokeSearcher;
use App\Models\Language;
use App\Models\ListSearcher;
use App\Models\Rest;
use App\Models\Vod;
use App\Models\VodList;
use App\Models\VodSearcher;
use Breeze\Helpers\Url;
use Illuminate\Database\Capsule\Manager as Capsule;

class iptvService extends common
{
    /**
     * 取首页推荐
     * @return array
     */
    public function vodHome(): array
    {
        $query = Capsule::table('iptv_vod')->select(['vod_id', 'vod_cid', 'vod_name', 'vod_ename', 'vod_type', 'vod_actor', 'vod_director', 'vod_content', 'vod_pic', 'vod_year', 'vod_addtime', 'vod_filmtime', 'vod_ispay', 'vod_price', 'vod_trysee', 'vod_url', 'vod_gold', 'vod_length', 'vod_multiple']);
        $vods = $query->where('vod_home', '=', '1')->get();

        if ($vods->count() <= 0) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        $vods = ArrayHelper::toArray($vods);

        array_walk($vods, function(&$v) {
            $v['_links'] = [
                'self'      => [Url::to('vods/' . $v['vod_id'], ['expand' => 'vodLinks'])],
                'recommend' => [Url::to('recommend/' . $v['vod_id'])]
            ];
        });

        return ['status' => true, 'data' => $vods];
    }

    /**
     * 获取片源推荐
     * @param $id int 影片id
     * @param $num int 数量
     * @return array
     */
    public function getRecommends($id, $num): array
    {
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
                'self'      => [ Url::to('vods/' . $v['vod_id'], ['expand' => 'vodLinks'])],
                'recommend' => [ Url::to('recommend/' . $v['vod_id'])]
            ];
        });

        return ['status' => true, 'data' => $vods];
    }

    /**
     * 获取片链接
     * @param  $vod_id string 影片id
     * @return array
     */
    public function getVodLinks($vod_id): array
    {
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
     * @param $id int
     * @param $expand string 扩展数据
     * @return array
     */
    public function getVod($id, $expand): array
    {
        $vod = Capsule::table('iptv_vod')
                    ->select(['vod_id', 'vod_cid' , 'vod_name', 'vod_ename', 'vod_type', 'vod_actor', 'vod_director', 'vod_content', 'vod_pic', 'vod_year' , 'vod_addtime', 'vod_filmtime', 'vod_ispay', 'vod_price', 'vod_trysee', 'vod_gold', 'vod_length', 'vod_multiple', 'vod_language', 'vod_area'])
                    ->where('vod_id', '=', $id)
                    ->first();

        if (is_null($vod)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        $vod = ArrayHelper::toArray($vod);

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

                $vod['vodLinks']['total'] = $total;
                $vod['vodLinks']['links']['items'] = $links;
            }
        }

        if ($expand == 'groupLinks') {
            $query = Capsule::table('iptv_play_group')
                            ->select('*')
                            ->where('vod_id', '=', $id)
                            ->where('use_flag', '=', 1);

            $total = $query->count();

            if ($total) {
                $vod['groupLinks']['total'] = $total;
                $vod['groupLinks']['items'] = [];

                $playGroups = ArrayHelper::toArray($query->get());
                foreach ($playGroups as $playGroup) {
                    $links = Capsule::table('iptv_vodlink')
                                ->select(['id', 'episode', 'plot', 'pic'])
                                ->where('group_id', '=', $playGroup['id'])
                                ->orderBy('episode', 'asc')
                                ->get();

                    $data = [];
                    $data['id']         = $playGroup['id'];
                    $data['vod_id']     = $playGroup['vod_id'];
                    $data['group_name'] = $playGroup['group_name'];
                    $data['sort']       = $playGroup['sort'];

                    $links = ArrayHelper::toArray($links);
                    if (!empty($links)) {
                        array_walk($links, function(&$v) {
                            $v['_links']['self']['href'] = Url::to("vod-links/{$v['id']}", ['access-token' => '']);
                        });
                        $data['items'] = $links;
                    }

                    $vod['groupLinks']['items'][] = $data;
                }

            }
        }

        $vod['_links'] = [
            'self'       => ['href' => Url::to("vods/{$id}", ["expand" => "vodLinks"])],
            'groupLinks' => ['href' => Url::to("vods/{$id}", ["expand" => "groupLinks"])],
            'recommend'  => ['href' => Url::to("recommends/{$id}")]
        ];

        return ['status' => true, 'data' => $vod];
    }

    /**
     * 获取影片列表
     * @param VodSearcher $searcher
     * @return array
     */
     public function getVods(VodSearcher $searcher): array
     {
         if (!$searcher->cid && $searcher->genre) {
             $vodList = VodList::findByDirName($searcher->genre);
             if ($vodList === false) {
                 return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
             }

             $searcher->cid = $vodList->list_id;
         }

         $query = Capsule::table('iptv_vod')->select(['vod_id', 'vod_cid', 'vod_name', 'vod_ename', 'vod_type', 'vod_actor', 'vod_director', 'vod_content', 'vod_pic', 'vod_year', 'vod_addtime', 'vod_filmtime', 'vod_ispay', 'vod_price', 'vod_trysee', 'vod_url', 'vod_gold', 'vod_length', 'vod_multiple']);
         $searcher->setQuery($query);
         $searcher->filterWhere($searcher->cid,  ['vod_cid', '=', $searcher->cid]);
         $searcher->filterWhere($searcher->name, ['vod_name', 'like', '%'.$searcher->name.'%']);
         $searcher->filterWhere($searcher->year, ['vod_year', '=', $searcher->year]);
         $searcher->filterWhere($searcher->type, ['vod_type', 'like', '%'.$searcher->type.'%']);
         $searcher->filterWhere($searcher->area, ['vod_area', 'like', '%'.$searcher->area.'%']);
         $searcher->filterWhere($searcher->letter, ['vod_letter', '=', $searcher->letter]);
         $searcher->filterWhere($searcher->keyword, ['vod_keywords', 'like', '%'.$searcher->keyword.'%']);

         $params        = $searcher->getLinkParams(['cid','name','per_page','page']);
         $data['_meta'] = $searcher->getRestMeta();

         $searcher->getQuery()
                  ->orderBy('sort', 'asc')
                  ->orderBy('vod_addtime', 'desc');

         $vods = $searcher->getDataByPage();

         if (count($vods) <= 0) {
             return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
         }

         $vods = ArrayHelper::toArray($vods);
         array_walk($vods, function(&$v) {
             $v['_links'] = [
                 'self'       => [Url::to('vods/' . $v['vod_id'], ['expand' => 'groupLinks'])],
                 'groupLinks' => ['href' => Url::to("vods/{$v['vod_id']}", ["expand" => "groupLinks"])],
                 'episodes'   => [Url::to("vods/{$v['vod_id']}", ["expand" => "groupLinks"])],
                 'recommend'  => [Url::to('recommend/' . $v['vod_id'])]
             ];
         });

         $data['items']  = $vods;
         $data['_links'] = Rest::setLinks($data['_meta'],'vods', $params, 'page');

         return ['status' => true, 'data' => $data];
     }

    /**
     * 获取影片类型
     * @param $expand
     * @return array
     */
     public function getType($expand): array
     {
         $genres = Capsule::table('iptv_list')->select(['list_id', 'list_name', 'list_dir', 'list_ispay', 'list_price', 'list_trysee', 'list_icon'])->get();
         if (count($genres) <= 0) {
             return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
         }

         $genres = ArrayHelper::toArray($genres);

         array_walk($genres, function(&$v) use($expand) {

             if ($expand == 'condition') {
                 $items = VodList::getAllSearchItemsByListID($v['list_id']);
                 $data = [];
                 if (!empty($items)) {
                     foreach ($items as $item) {
                         $data[$item->field]['name']    = $item->name;
                         $data[$item->field]['field']   = $item->field;
                         $data[$item->field]['items'][] = ['name' => $item->itemName, 'zh_name' => $item->zh_name];
                     }
                 }
                 $v['condition'] = array_values($data);
             }

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
     * @return array
     */
     public function getBanner(): array
     {
         $query = Capsule::table('sys_banner')->select('*');

         $canSortField = ['id', 'vod_id', 'sort', 'title'];
         foreach ($canSortField as $field) {
             if ($fieldValue = $this->request->get($field)) {
                 if ($fieldValue == $field) {
                     $query->orderBy($field, 'asc');
                 } else {
                     $query->orderBy($field, 'desc');
                 }
             }
         }

         $banners = $query->get();

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


    /**
     * 获取左侧/顶部/底部 栏目
     * @param $type string 如Movie
     * @param $language string 如Movie
     * @return array
     */
    public function getCategory($type, $language='en_US'): array
    {
        $type = ucfirst(strtolower($type));
        // 根据type查找cid
        $vods = VodList::findByDirName($type);
        if (is_null($vods)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        $items = VodList::getAllSearchItemsByListID($vods->list_id);

        if (empty($items)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        $itemsI18nData = Language::getItemsI18n($items, $language);
        $typesI18nData = Language::getTypeI18n($language);

        $data = [];
        foreach ($items as $item) {

            $data[$item->field]['name']        = isset($typesI18nData[$item->id]) ?  $typesI18nData[$item->id] : $item->name;
            $data[$item->field]['field']       = ucfirst($item->field);
            $data[$item->field]['image']       = !empty($item->image)? Func::getAccessUrl('287994000', $item->image, 13086400) : 'https://s1.ax1x.com/2018/11/14/ijMGqK.png';
            $data[$item->field]['image_hover'] = !empty($item->image_hover)? Func::getAccessUrl('287994000', $item->image_hover, 13086400) : 'https://s1.ax1x.com/2018/11/14/ijMGqK.png';
            $data[$item->field]['items'][]     = isset($itemsI18nData[$item->bid]) ? ['name' => $item->itemName, 'i18n'=> $itemsI18nData[$item->bid]] : ['name' => $item->itemName, 'i18n'=> $item->itemName];
            $data[$item->field]['_links']      = ['self' => Url::to('iptv/' . $item->field, ['type' => $type, 'lang' => $language])];
        }

        if (empty($data)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        return ['status' => true, 'data' => $data];
    }

    public function getDimensionData($type, $mode = 'hot', $language='en-us'): array
    {
        // 计算type的offset
        $type_params = $this->getTypeParams();

        list($type_offset, $type_limit) = $this->getOffset($type_params['type_page'], $type_params['type_perpage']);

        // 计算items的offset
        $params = $this->getTypeItemParams();
        list($items_offset, $items_limit) = $this->getOffset($params['items_page'], $params['items_perpage']);

        // 根据type查找cid
        $vods = VodList::findByDirName($type);
        
        if (!$vods) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        $list_id = $vods->list_id;
        $typesArr = VodList::getPartOfItemsByListID($list_id, $mode);

        if (empty($typesArr)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        $data       = [];
        $type_meta  = [
            'totalCount'  => count($typesArr),
            'pageCount'   => ceil(count($typesArr)/$type_params['type_perpage']),
            'currentPage' => $type_params['type_page'],
            'perPage'     => $type_params['type_perpage']
        ];
        // 因为type 是存到一个字段里面的 所以要通过array_slice 达到'分页'效果
        $typesArr = array_slice($typesArr, $type_offset, $type_limit);

        foreach ($typesArr as $str) {

            $query = Vod::getDimensionQueryByListID($list_id, $mode, $str);
            $total = $query->count();
            $vods  = $query->limit($items_limit)->offset($items_offset)->get();

            $vods   = ArrayHelper::toArray($vods);
            $vods   = $this->setItemLink($vods);

            $_meta  = [
                'totalCount'  => $total,
                'pageCount'   => ceil($total/$params['items_perpage']),
                'currentPage' => $params['items_page'],
                'perPage'     => $params['items_perpage']
            ];

            $params['field'] = 'hot';
            $params['genre'] = $str;
            $params['cid']   = $list_id;

            $params = array_reverse($params);

            $_links = Rest::setLinks($_meta,'iptv/list', $params, 'items_page');

            // i18n
            if ($total) {
                $data[] = [
                    'type'   => Language::translate($str, $language, 'iptv_type_item'),
                    'items'  => $vods,
                    '_links' => $_links,
                    '_meta'  => $_meta,
                    'total'  => $total
                ];
            }
        }

        if (empty($data)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        array_multisort(array_column($data, 'total'),SORT_DESC, $data);

        $type_params['type'] = $type;
        $type_params['lang'] = $language;

        $finalData = [
            'items'  => $data,
            '_links' => Rest::setLinks($type_meta,'iptv/'.$mode, $type_params, 'type_page'),
            '_meta'  => $type_meta
        ];

        return ['status' => true, 'data' => $finalData];
    }

    private function getTypeParams(): array
    {
        // 计算type的offset
        $type_params['type_perpage']  = $this->request->get('type_perpage', 6);
        $type_params['type_page']     = $this->request->get('type_page', 1);

        return $type_params;
    }

    private function getTypeItemParams(): array
    {
        $params['items_perpage'] = $this->request->get('items_perpage', 12);
        $params['items_page']    = $this->request->get('items_page', 1);

        return $params;
    }

    private function getOffset($page, $perpage): array
    {
        $type_offset  = ($page - 1) * $perpage;
        $type_limit   =  $perpage;

        return [$type_offset, $type_limit];
    }

    /**
     * 获取分页加载
     * @param ListSearcher $searcher
     * @return array
     */
    public function getList(ListSearcher $searcher): array
    {
        $cid    = $searcher->cid;
        $field  = $searcher->field;
        $params = $searcher->getLinkParams(['cid', 'genre', 'items_perpage', 'items_page']);

        $query = Capsule::table('iptv_vod')
                            ->select(['vod_id', 'vod_cid', 'vod_name', 'vod_ename', 'vod_type', 'vod_actor', 'vod_director', 'vod_content', 'vod_pic', 'vod_year', 'vod_addtime', 'vod_filmtime', 'vod_ispay', 'vod_price', 'vod_trysee', 'vod_url', 'vod_gold', 'vod_length', 'vod_multiple'])
                            ->where('vod_cid', '=', $cid)
                            ->orderBy('sort', 'asc')
                            ->orderBy('vod_addtime', 'desc');

        $searcher->setQuery($query);

        // 查询
        switch ($field)
        {
            case 'hot':
                $searcher->filterWhere($searcher->genre, ['vod_type', 'like', "%{$searcher->genre}%"]);
                $searcher->getQuery()->orderBy('vod_hits', 'desc');
                break;
            case 'type':
                $searcher->filterWhere($searcher->genre, ['vod_type', 'like', "%{$searcher->genre}%"]);
                $searcher->getQuery()->orderBy('vod_year', 'desc');
                break;
            case 'language':
            case 'area':
            case 'year':
                $searcher->filterWhere($searcher->genre, ['vod_type', '=', "%{$searcher->genre}%"]);
                $searcher->getQuery()->orderBy('vod_year', 'desc');
                break;
        }

        $meta  = $searcher->getRestMeta('items_page', 'items_perpage');
        $vods  = $searcher->getDataByPage('items_page', 'items_perpage');
        $vods  = ArrayHelper::toArray($vods);
        $vods  = $this->setItemLink($vods);
        $_links = Rest::setLinks($meta,'iptv/list', $params, 'items_page');

        $data = [
            '_meta'  => $meta,
            'items'  => $vods,
            '_links' => $_links
        ];

        return ['status' => true, 'data' => $data];
    }

    private function setItemLink($vods): array
    {
        if ($vods) {
            array_walk($vods, function(&$vod) {
                $vod['_links'] = [
                    'self'       => ['href' => Url::to("vods/{$vod['vod_id']}", ["expand" => "vodLinks"])],
                    'groupLinks' => ['href' => Url::to("vods/{$vod['vod_id']}", ["expand" => "groupLinks"])],
                    'recommend'  => ['href' => Url::to("recommends/{$vod['vod_id']}")]
                ];
            });
        }

        return $vods;
    }

    public function getCondition($list_id): array
    {
        $items = VodList::getAllSearchItemsByListID($list_id);

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
     * @param KaraokeSearcher $searcher
     * @return array
     */
     public function getKaraokeList(KaraokeSearcher $searcher): array
     {
         list($field, $direct) = explode(':',$searcher->sort);

         $orderMaps = [
             'update_time' => 'utime',
             'popular'     => 'hit_count'
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

         $query = Capsule::table('sys_karaoke')->select(['albumName','albumImage','url','area','year','tags','yesterday_viewed']);

         $searcher->setQuery($query);
         $searcher->filterWhere($searcher->name, ['name', 'like', "%{$searcher->name}%"]);
         $searcher->filterWhere($searcher->lang, ['lang', '=', $searcher->lang]);
         $searcher->filterWhere($searcher->tags, ['tags', '=', $searcher->tags]);

         $totalCount = $searcher->getTotal();

         if ($totalCount == false) {
             return ['status' => false, 'data' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
         }

         $searcher->getQuery()->orderBy($orderField, $direct);
         $data      = $searcher->getDataByPage('page', 'perPage');

         $data = ArrayHelper::toArray($data);

         $cacheData['page']       = $searcher->page;
         $cacheData['perPage']    = $searcher->perPage;
         $cacheData['totalPage']  = $searcher->getPageCount($totalCount, 'perPage');
         $cacheData['totalItems'] = $totalCount;
         $cacheData['lang']       = 'Vietnamese,Chinese,English,Korean,French,Other';
         $cacheData['data']       = $data;

         $this->getRedis()->set("karaoke:$searchKey", json_encode($cacheData));
         $this->getRedis()->expire("karaoke:$searchKey", 86400);

         return ['status' => true, 'data' => $cacheData];
     }

    /**
     * 取卡拉ok
     * @param $url string
     * @return array
     */
    public function getKaraoke($url): array
    {
        $cacheKey = "karaoke-{$url}";
        $cacheDB = Redis::$REDIS_VOD_ALBUM;
        $cacheData = $this->getDataFromCache($cacheKey, $cacheDB);

        if ($cacheData) {
            return ['status' => true, 'data' => $cacheData];
        }

        $data = file_get_contents("http://148.72.168.63:10082/bak/youtube.php?id={$url}");
        if ($data) {
            $link = [
                'hd720' => $data,
                'medium' => $data,
                'small' => $data
            ];
        } else {
            $link = [
                'hd720' => 'http://img.ksbbs.com/asset/Mon_1703/05cacb4e02f9d9e.mp4',
                'medium' => 'http://img.ksbbs.com/asset/Mon_1703/05cacb4e02f9d9e.mp4',
                'small' => 'http://img.ksbbs.com/asset/Mon_1703/05cacb4e02f9d9e.mp4'
            ];
        }

        $this->redis = $this->getRedis($cacheDB);

        $this->redis->set($cacheKey, json_encode($link));
        $this->redis->expire($cacheKey , 3600);

        return ['status' => true, 'data' => $link];
    }


    public function getLink($id): array
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