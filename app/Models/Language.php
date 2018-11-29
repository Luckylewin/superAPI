<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/11/27
 * Time: 14:08
 */

namespace App\Models;

use App\Components\helper\ArrayHelper;
use Illuminate\Database\Capsule\Manager as Capsule;

class Language
{
    public static function translate($query, $to, $table)
    {
        $i18n = Capsule::table('sys_multi_lang')->select('value')
            ->where(['table' => $table, 'origin' => $query, 'language' => $to])
            ->first();

        if (!empty($i18n)) {
            return $i18n->value;
        }

        return $query;
    }

    public static function getItemsI18n($items, $language)
    {
        $itemsI18nData = [];
        $ids = array_column(ArrayHelper::toArray($items), 'bid');
        $i18n = Capsule::table('sys_multi_lang')
            ->select(['fid','value'])
            ->whereIn('fid', $ids)
            ->where('table', '=', 'iptv_type_item')
            ->where('language', '=', $language)
            ->where('field', '=', 'name')
            ->get()
            ->toArray();

        if (!empty($i18n)) {
            foreach ($i18n as $val) {
                $itemsI18nData[$val->fid] = $val->value;
            }
        }

        return $itemsI18nData;
    }

    public static function getTypeI18n($language)
    {
        $typesI18nData = [];
        $i18n = Capsule::table('sys_multi_lang')
            ->select(['fid','value'])
            ->where('table', '=', 'iptv_type')
            ->where('language', '=', $language)
            ->get()
            ->toArray();

        if (!empty($i18n)) {
            foreach ($i18n as $val) {
                $typesI18nData[$val->fid] = $val->value;
            }
        }

        return $typesI18nData;
    }

    public static function getLanguages()
    {
        return [
            'en_US'=>'英语',
            'zh_CN'=>'中文',
            'es_ES'=>'西班牙语',
            'pt_PT'=>'葡萄牙语',
            'vi_VN'=>'越南语',
            'ar_AE'=>'阿拉伯语',
            'pt_BR'=>'葡萄牙语(巴西)',
            'es_US'=>'西班牙语(美国)',
            'zh_TW'=>'中文台湾',
            'zh_HK'=>'中文香港',
            'th_TH'=>'泰语',
            'fr_FR'=>'法语',
            'da_DK'=>'丹麦语',
            'de_DE'=>'德语',
            'ru_RU'=>'俄语',
            'it_IT'=>'意大利语',
            'ja_JP'=>'日语',
            'ko_KR'=>'韩语',
            'sv_SE'=>'瑞典语',
            'el_GR'=>'希腊语'
        ];
    }
}