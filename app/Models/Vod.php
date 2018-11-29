<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/20
 * Time: 10:17
 */

namespace App\Models;

use Illuminate\Database\Capsule\Manager as Capsule;
use App\Components\helper\ArrayHelper;
use Illuminate\Database\Query\Builder;

class Vod
{
    /**
     *
     * @param $list_id
     * @param $field
     * @param $value
     * @return Builder
     */
    public static function getDimensionQueryByListID($list_id, $field, $value)
    {
        $query = Capsule::table('iptv_vod')
                                ->select(['vod_id', 'vod_cid', 'vod_name', 'vod_ename', 'vod_type', 'vod_actor', 'vod_director', 'vod_content', 'vod_pic', 'vod_year', 'vod_addtime', 'vod_filmtime', 'vod_ispay', 'vod_price', 'vod_trysee', 'vod_url', 'vod_gold', 'vod_length', 'vod_multiple'])
                                ->where('vod_cid', '=', $list_id);
        switch ($field)
        {
            case 'year':
                $query->where('vod_year', '=', $value)
                      ->orderBy('vod_hits', 'desc');
                break;
            case 'hot':
                $query->where('vod_type', 'like', "%$value%")
                      ->orderBy('vod_hits', 'desc');
                break;
            case 'type':
                $query->where('vod_type', 'like', "%$value%")
                      ->orderBy('vod_year', 'desc');
                break;
            case 'area':
                $query->where('vod_area', '=', $value)
                      ->orderBy('vod_year', 'desc');
                break;
            case 'language':
                $query->where('vod_language', '=', $value)
                    ->orderBy('vod_year', 'desc');
                break;
        }

        $query->orderBy('sort', 'asc');

        return $query;
    }

    public static function getAllValueByListID($list_id, $field, $order = 'desc')
    {
        $types = Capsule::table('iptv_vod')
            ->select($field)
            ->where('vod_cid', '=', $list_id)
            ->distinct()
            ->get();

        $years = ArrayHelper::toArray($types);
        $yearArr = array_filter($years);
        if ($order == 'desc') {
            rsort($yearArr);
        } else {
            asort($yearArr);
        }

        if (empty($yearArr)) {
            return false;
        }

        return array_column($yearArr, $field);
    }


    /**
     * 获取所有分类
     * @param $list_id
     * @return array|bool
     */
    public static function getAllTagByListID($list_id)
    {
        $types = Capsule::table('iptv_vod')
                        ->select('vod_type')
                        ->where('vod_cid', '=', $list_id)
                        ->distinct()
                        ->get();

        $types = ArrayHelper::toArray($types);
        $typesArr = [];
        array_walk($types, function($v) use(&$typesArr) {
            $typesArr = array_merge($typesArr, explode(',', $v['vod_type']));
        });
        $typesArr = array_unique($typesArr);

        if (empty($typesArr)) {
            return false;
        }

        return $typesArr;
    }
}