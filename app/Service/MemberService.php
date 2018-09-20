<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/20
 * Time: 14:40
 */

namespace App\Service;

use App\Exceptions\ErrorCode;
use Illuminate\Database\Capsule\Manager as Capsule;

class MemberService extends common
{
    public function getPrice()
    {
       $price = Capsule::table('ott_price_list')->get()->toArray();

       if (empty($price)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
       }

       $data = [];
       $titles = ['1' => '一个月', '3' => '三个月', '6' => '六个月', '12' => '一年'];

       foreach ($price as $val) {
           $data[] = [
                'type' => $val->id,
                'title' => isset($titles[$val->value]) ? $titles[$val->value] : $val->value,
                'price' => $val->price
           ];
       }

       return ['status' => true, 'data' => $data];
    }
}