<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/11/14
 * Time: 15:56
 */

namespace App\Models;

use App\Components\helper\ArrayHelper;
use Illuminate\Database\Capsule\Manager as Capsule;

class VodList
{

    public static function getAllSearchItemsByDir($type)
    {
        $type = ucfirst($type);

        $list = Capsule::table('iptv_list')
                    ->where('list_dir', $type)
                    ->first();

        if ($list) {
            return self::getAllSearchItemsByListID($list->list_id);
        }

        return false;
    }

    public static function getAllSearchItemsByListID($list_id)
    {
        $data =  Capsule::table('iptv_type AS a')
            ->where('vod_list_id', '=', $list_id)
            ->select(['a.*', 'b.name as itemName','b.zh_name','b.exist_num','b.id as bid'])
            ->orderBy('a.sort', 'asc')
            ->orderBy('b.sort', 'asc')
            ->leftJoin('iptv_type_item AS b', 'b.type_id', '=', 'a.id')
            ->where('b.exist_num', '>', 0)
            ->where('b.is_show', '=', 1)
            ->get()
            ->toArray();

        return $data;

    }


    public static function getPartOfItemsByListID($list_id, $type)
    {
        $items =  Capsule::table('iptv_type AS a')
                ->where('vod_list_id', '=', $list_id)
                ->select(['a.*', 'b.name as itemName','b.zh_name','b.exist_num'])
                ->leftJoin('iptv_type_item AS b', 'b.type_id', '=', 'a.id')
                ->where('b.exist_num', '>', 0)
                ->where('b.is_show', '=', 1)
                ->where('a.field', '=', $type)
                ->get();

        if (empty($items)) {
            return [];
        }

        $items = ArrayHelper::toArray($items);
        return array_column($items, 'itemName');
    }

    public static function findByDirName($dir)
    {
        $vods = Capsule::table('iptv_list')
                         ->select('list_id')
                         ->where('list_dir', '=', $dir)
                         ->first();

        if (is_null($vods)) {
            return false;
        }

        return $vods;
    }
}