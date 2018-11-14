<?php
namespace App\Service;

use App\Components\cache\Redis;
use App\Components\helper\ArrayHelper;
use App\Components\helper\Func;
use App\Exceptions\ErrorCode;
use App\Models\Vod;
use App\Models\VodList;
use Breeze\Helpers\Url;
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
                    ->select(['vod_id', 'vod_cid' , 'vod_name', 'vod_ename', 'vod_type', 'vod_actor', 'vod_director', 'vod_content', 'vod_pic', 'vod_year' , 'vod_addtime', 'vod_filmtime', 'vod_ispay', 'vod_price', 'vod_trysee', 'vod_gold', 'vod_length', 'vod_multiple', 'vod_language', 'vod_area'])
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
                                ->orderBy('episode', 'desc')
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
            'self' => ['href' => Url::to("vods/{$id}", ["expand" => "vodLinks"])],
            'groupLinks' => ['href' => Url::to("vods/{$id}", ["expand" => "groupLinks"])],
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
         $cid   = $this->request->get('cid')  ?? ($this->request->get('vod_cid') ?? '');
         $name  = $this->request->get('name') ?? ($this->request->get('vod_name') ?? '');
         $type  = $this->request->get('type') ?? ($this->request->get('vod_type') ?? '');
         $year  = $this->request->get('year') ?? ($this->request->get('vod_year') ?? '');
         $area  = $this->request->get('area') ?? ($this->request->get('vod_language') ?? '');
         $cat   = $this->request->get('cat')  ?? '';

         $per_page = $this->request->get('per_page') ?? 12;
         $page     = $this->request->get('page') ?? 1;


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

         if ($cat) {
            switch ($cat)
            {
                case 'hot':
                    $query->orderBy('vod_hits', 'desc');
                    break;
            }
         }

         $totalCount = $query->count();
         $pageCount = ceil($totalCount / $per_page);

         if ($page > $pageCount) {
             $page = $pageCount;
         }

         $offset = ($page - 1) * $per_page;

         // 参数检查
         $params = [
                    'cid'       => $cid,
                    'name'      => $name,
                    'per_page'  => $per_page,
                    'page'      => $page,

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
                 'self' => [Url::to('vods/' . $v['vod_id'], ['expand' => 'groupLinks'])],
                 'groupLinks' => ['href' => Url::to("vods/{$v['vod_id']}", ["expand" => "groupLinks"])],
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

         $data['_links'] = $this->setLinks($data['_meta'],'vods', $params, 'page');

         return ['status' => true, 'data' => $data];
     }

     public function getYear()
     {
         $type = $this->request->get('type', 'Movie');

         // 根据type查找cid
         $vods = Capsule::table('iptv_list')
                         ->select('list_id')
                         ->where('list_dir', '=', $type)
                         ->first();

         if (is_null($vods)) {
             return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
         }
     }

    /**
     * 设置资源描述
     * @param $meta
     * @param $route
     * @param $params
     * @param $pageField
     * @return array
     */
     private function setLinks($meta, $route, $params, $pageField='page')
     {
         if ($params[$pageField] == 1) {
             if ($meta['pageCount'] == 1) {
                 return [
                     'self' => $this->setSelf($route,$params, $pageField)
                 ];
             } else {
                 return [
                     'self' => $this->setSelf($route, $params, $pageField),
                     'next' => $this->setNext($route, $params, $pageField),
                     'last' => $this->setLast($route, $params, $meta, $pageField)
                 ];
             }
         } else if ($params[$pageField] < $meta['totalCount']) {
             return [
                 'self' => $this->setSelf($route, $params, $pageField),
                 'first' => $this->setFirst($route, $params, $pageField),
                 'prev' => $this->setPrev($route, $params, $pageField),
                 'next' => $this->setNext($route, $params, $pageField),
                 'last' => $this->setLast($route, $params, $meta, $pageField)
             ];
         } else {
             return [
                 'self' => $this->setSelf($route, $params,  $pageField),
                 'first' => $this->setFirst($route, $params,  $pageField),
                 'prev' => $this->setPrev($route, $params, $pageField),
             ];
         }
     }

     private function setSelf($route, $params, $pageField)
     {
         return ['href' => Url::to($route, $params)];
     }

     private function setFirst($route, $params, $pageField)
     {
         $params[ $pageField] = 1;
         return ['href' => Url::to($route, $params)];
     }

     private function setPrev($route, $params, $pageField)
     {
        $params[ $pageField]--;
        return ['href' => Url::to($route, $params)];
     }

     private function setNext($route, $params, $pageField)
     {
         $params[$pageField]++;
         return ['href' => Url::to($route, $params)];
     }

     private function setLast($route, $params, $meta, $pageField)
     {
        $params[$pageField] = $meta['pageCount'];
        return ['href' => Url::to($route, $params)];
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

         $expand = $this->request->get('expand');
         $genres = ArrayHelper::toArray($genres);



         array_walk($genres, function(&$v) use($expand) {

             if ($expand == 'condition') {
                 $items = Capsule::table('iptv_type AS a')
                                 ->where('vod_list_id', '=', $v['list_id'])
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


     public function getBanner()
     {
         $query = Capsule::table('sys_banner')
                            ->select('*');

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
     * @return array
     */
    public function getCategory()
    {
        $type = $this->request->get('type', 'Movie');
        $type = ucfirst(strtolower($type));
        // 根据type查找cid
        $vods = VodList::findByDirName($type);
        if (is_null($vods)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        $items = VodList::getAllSearchItemsByListID($vods->list_id);

        $data = [];
        foreach ($items as $item) {
            $data[$item->field]['name']  = $item->name;
            $data[$item->field]['field'] = ucfirst($item->field);
            $data[$item->field]['image'] = !empty($item->image)? Func::getAccessUrl('287994000', $item->image, 13086400) : 'https://s1.ax1x.com/2018/11/14/ijMGqK.png';
            $data[$item->field]['items'][] = ['name' => $item->itemName, 'zh_name' => $item->zh_name];
            $data[$item->field]['_links'] = [
                'self' => Url::to('iptv/' . $item->field, ['type' => $type])
            ];
        }

        if (empty($data)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        return ['status' => true, 'data' => $data];
    }

    public function getDimensionData($mode = 'hot')
    {
        $type = $this->request->get('type', 'Movie');
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

        switch ($mode)
        {
            case 'year':
                $typesArr = Vod::getAllValueByListID($list_id, 'vod_year');
                break;
            case 'area':
                $typesArr = Vod::getAllValueByListID($list_id, 'vod_area');
                break;
            case 'language':
                $typesArr = Vod::getAllValueByListID($list_id, 'vod_language');
                break;
            case 'hot':
                $typesArr = Vod::getAllTagByListID($list_id);
                break;
            case 'type':
                $typesArr = Vod::getAllTagByListID($list_id);
                break;
            default:
                $typesArr = [];
        }

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

            $_links = $this->setLinks($_meta,'iptv/list', $params, 'items_page');

            if ($total) {
                $data[] = [
                    'type'   => $str,
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

        $finalData = [
            'items'  => $data,
            '_links' => $this->setLinks($type_meta,'iptv/'.$mode, $type_params, 'type_page'),
            '_meta'  => $type_meta
        ];

        return ['status' => true, 'data' => $finalData];
    }

    private function getTypeParams()
    {
        // 计算type的offset
        $type_params['type_perpage']  = $this->request->get('type_perpage', 6);
        $type_params['type_page']     = $this->request->get('type_page', 1);

        return $type_params;
    }

    private function getTypeItemParams()
    {
        $params['items_perpage'] = $this->request->get('items_perpage', 12);
        $params['items_page']    = $this->request->get('items_page', 1);

        return $params;
    }

    private function getOffset($page, $perpage)
    {
        $type_offset  = ($page - 1) * $perpage;
        $type_limit   =  $perpage;

        return [$type_offset, $type_limit];
    }

    public function getList()
    {
        $cid   = $this->request->get('cid');
        $genre = $this->request->get('genre');
        $field = $this->request->get('field', 'hot');
        $params['items_perpage'] = $this->request->get('items_perpage', 12);
        $params['items_page']    = $this->request->get('items_page', 1);
        $items_offset = ($params['items_page'] - 1) * $params['items_perpage'];
        $items_limit  = $params['items_perpage'];

        $query = $query = Capsule::table('iptv_vod')
                            ->select(['vod_id', 'vod_cid', 'vod_name', 'vod_ename', 'vod_type', 'vod_actor', 'vod_director', 'vod_content', 'vod_pic', 'vod_year', 'vod_addtime', 'vod_filmtime', 'vod_ispay', 'vod_price', 'vod_trysee', 'vod_url', 'vod_gold', 'vod_length', 'vod_multiple'])
                            ->where('vod_cid', '=', $cid);

        // 查询
        switch ($field)
        {
            case 'hot':
                $query->where('vod_type', 'like', "%$genre%")
                      ->orderBy('vod_hits', 'desc');
                break;
            case 'type':
                $query->where('vod_type', 'like', "%$genre%")
                      ->orderBy('vod_year', 'desc');
                break;
            case 'language':
            case 'area':
            case 'year':
                $query->where('vod_type', '=',$genre)
                      ->orderBy('vod_year', 'desc');
                break;
        }


        $total = $query->count();

        $vods = $query->orderBy('vod_year', 'desc')
                    ->limit($items_limit)
                    ->offset($items_offset)
                    ->get();

        $vods   = ArrayHelper::toArray($vods);
        $vods   = $this->setItemLink($vods);

        $_meta  = [
            'totalCount'  => $total,
            'pageCount'   => ceil($total/$params['items_perpage']),
            'currentPage' => $params['items_page'],
            'perPage'     => $params['items_perpage']
        ];

        $params['cid']   = $cid;
        $params['genre'] = $genre;
        $params = array_reverse($params);

        $_links = $this->setLinks($_meta,'iptv/list', $params, 'items_page');
        $data = [
            'items'  => $vods,
            '_links' => $_links,
            '_meta'  => $_meta,
        ];

        return ['status' => true, 'data' => $data];
    }

    private function setItemLink($vods)
    {
        array_walk($vods, function(&$vod) {
            $vod['_links'] = [
                'self' => ['href' => Url::to("vods/{$vod['vod_id']}", ["expand" => "vodLinks"])],
                'groupLinks' => ['href' => Url::to("vods/{$vod['vod_id']}", ["expand" => "groupLinks"])],
                'recommend' => ['href' => Url::to("recommends/{$vod['vod_id']}")]
            ];
        });

        return $vods;
    }

    public function getCondition()
    {
        $vod_id = $this->request->get('vod_id');

        if (empty($vod_id)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_PARAMETER];
        }

        $items = Capsule::table('iptv_type AS a')
                        ->where('vod_list_id', '=', $vod_id)
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
             $page = $this->post('page', '1');
             $perPage = $this->post('perPage', 10);
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

         //计算总页
         $totalPage = self::getTotalPage($perPage, $totalItems);

         //计算offset
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
         $cacheData['totalItems'] = $totalItems;
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
        try {
            $url = $this->post('url', null, ['string']);
        } catch (\Exception $e) {
            return ['status' => false, 'code'=>ErrorCode::$RES_ERROR_PARAMETER_MISSING];
        }

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


    public function getLink($id)
    {
        if (empty($id)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_PARAMETER_MISSING];
        }

        $link = Capsule::table('iptv_vodlink')->where('id', '=', $id)->first();

        // 查询这个link的分组名称
        // $group = Capsule::table('iptv_play_group')->where('group_id', '=', $link->group_id)->first();

        if (is_null($link)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        $link = ArrayHelper::toArray($link);

        return ['status' => true, 'data' => $link];
    }

}