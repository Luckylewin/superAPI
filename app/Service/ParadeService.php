<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/12/26
 * Time: 10:05
 */

namespace App\Service;

use App\Exceptions\ErrorCode;
use Illuminate\Database\Capsule\Manager as Capsule;

class ParadeService extends common
{
    public function getParadeSplitWithDate()
    {
        $parades = Capsule::table('iptv_parade')
                        ->orderBy('parade_date','asc')
                        ->get();

        if ($parades->count() == false) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        $paradeData = [];

        foreach ($parades as $parade) {
            if (isset($paradeData[$parade->parade_date]) == false) {
                $paradeData[$parade->parade_date] = [];
            }

            $paradeItems = json_decode($parade->parade_data, true);
            $paradeData[$parade->parade_date] = array_merge($paradeData[$parade->parade_date], array_values($paradeItems));
        }

        $dateList = array_keys($paradeData);

        foreach ($paradeData as $key => $dateItems) {
            array_multisort($dateItems, SORT_ASC, array_column($dateItems,'parade_timestamp'));
            $paradeData[$key] = $dateItems;
        }

        $data = [
            'dateList' => $dateList,
            'paradeData' => $paradeData
        ];

        return ['status' => true, 'data' => $data];
    }
}