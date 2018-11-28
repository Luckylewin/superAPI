<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/11/27
 * Time: 14:08
 */

namespace App\Models;

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

    public static function getLanguages()
    {
        return [
            'en-us'=>'英语',
            'zh-cn'=>'中文',
            'pt-br'=>'葡萄牙语',
            'vi-vn'=>'越南语',
            'es-es'=>'西班牙语',
            'ar'   =>'阿拉伯语',
            'th-th'=>'泰语',
            'fr-fr'=>'法语',
            'zh-tw'=>'繁体中文',
            'da-dk'=>'丹麦语',
            'nl-nl'=>'荷兰语',
            'fi-fi'=>'芬兰语',
            'de-de'=>'德语',
            'ru-ru'=>'俄语',
            'it-it'=>'意大利语',
            'ja-jp'=>'日语',
            'ko-kr'=>'韩语',
            'sv-se'=>'瑞典语',
            'el-gr'=>'希腊语',
            'pl-pl' => '波兰语',
            'fi-FI' => '芬兰语',
        ];
    }
}