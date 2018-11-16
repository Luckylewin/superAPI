<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/11/14
 * Time: 15:56
 */

namespace App\Models;

use Illuminate\Database\Capsule\Manager as Capsule;

class VodList
{
    public static function getAllSearchItemsByListID($list_id)
    {
        return Capsule::table('iptv_type AS a')
            ->where('vod_list_id', '=', $list_id)
            ->select(['a.*', 'b.name as itemName','b.zh_name','b.exist_num'])
            ->orderBy('a.sort')
            ->leftJoin('iptv_type_item AS b', 'b.type_id', '=', 'a.id')
            ->where('b.exist_num', '>', 0)
            ->where('b.is_show', '=', 1)
            ->get()
            ->toArray();
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