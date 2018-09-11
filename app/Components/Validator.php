<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/4
 * Time: 9:56
 */

namespace App\Components;


use App\Exceptions\ErrorCode;

class Validator
{

    public static function validate($ruleArray, $val)
    {
        $rule = $ruleArray[0];
        switch ($rule)
        {
            case 'integer':
                if (!is_numeric($val)) {
                    return ['status' => false, 'code' => ErrorCode::$RES_ERROR_PARAMETER];
                }

                if (isset($ruleArray['min'])) {
                    if ($val < $ruleArray['min']) {
                      return ['status' => false, 'code' => ErrorCode::$RES_ERROR_PARAMETER];
                    }
                }

                if (isset($ruleArray['max'])) {
                    if ($val > $ruleArray['max']) {
                        return ['status' => false, 'code' => ErrorCode::$RES_ERROR_PARAMETER];
                    }
                }
                break;
            case 'in':

                if (isset($ruleArray[1])) {
                    $range = $ruleArray[1];
                    if (!is_array($range)) {
                        throw new \Exception("请使用数组表示范围");
                    }
                    if (!in_array($val, $range)) {
                        return ['status' => false, 'code' => ErrorCode::$RES_ERROR_PARAMETER];
                    }
                } else {
                    throw new \Exception("请使用数组表示范围");
                }

                break;
            case 'string':
                $val = addslashes($val);
                break;
            case 'required':
                if (empty($val)) {
                    return ['status' => false, 'code' => ErrorCode::$RES_ERROR_PARAMETER];
                }
                break;
        }

        return ['status' => true, 'value' => $val];
    }
}