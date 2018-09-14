<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/3
 * Time: 11:42
 */
namespace App\Exceptions;

/**
 * 全局错误码
 * Class ErrorCode
 * @package App\Exceptions
 */
class ErrorCode
{
    public static $RES_SUCCESS = 0;
    public static $RES_ERROR_UID_NOT_IN_DATABASE = 1;
    public static $RES_ERROR_HEADER_OR_UID_NOT_SET = 2;
    public static $RES_ERROR_MESSAGE_IS_NOT_JSON = 3;
    public static $RES_ERROR_LONGITUDE_OR_LATITUDE_IS_NOT_SET = 4;
    public static $RES_ERROR_LONGITUDE_OR_LATITUDE_IS_NOT_NUMERIC = 5;
    public static $RES_ERROR_HEADER_NOT_FOUND = 6;
    public static $RES_ERROR_UID_NOT_MAC = 7;
    public static $RES_ERROR_UID_NOT_EXIST = 8;
    public static $RES_ERROR_UID_NOT_REGISTER = 9;
    public static $RES_ERROR_UID_IS_BLACK_LIST = 10;
    public static $RES_ERROR_OPERATE_DATABASE_ERROR = 11;
    public static $RES_ERROR_UID_ALREADY_EXIST = 12;
    public static $RES_ERROR_UID_NOT_ENABLE = 13;
    public static $RES_ERROR_UID_ABNORMAL = 14;
    public static $RES_ERROR_NO_UID_ONLINE = 15;
    public static $RES_ERROR_NO_NEED_UPDATE = 16;
    public static $RES_ERROR_NO_LIST_DATA = 17;
    public static $RES_ERROR_SIGNATURE = 18;
    public static $RES_ERROR_DATA_LEN = 19;
    public static $RES_ERROR_NO_HAVE_TID = 20;
    public static $RES_ERROR_NO_HAVE_AID = 21;
    public static $RES_ERROR_NO_HAVE_IID = 22;
    public static $RES_ERROR_TIMESTAMP = 23;
    public static $RES_ERROR_NO_RECORD = 24;
    public static $RES_ERROR_IP_FORBIDDEN = 25;
    public static $RES_ERROR_INVALID_TOKEN = 26;
    public static $RES_ERROR_TOKEN_EXPIRE = 27;
    public static $RES_ERROR_SN_NOT_EXIST = 28;
    public static $RES_ERROR_INVALID_SIGN = 29;
    public static $RES_ERROR_UID_EXPIRED = 30;
    public static $RES_ERROR_UID_RIGISTERED = 31;
    public static $RES_ERROR_PARAMETER_MISSING = 32;
    public static $RES_ERROR_INVALID_CARD = 33;
    public static $RES_ERROR_PARAMETER = 34;
    public static $RES_ERROR_FREE_OF_CHARGE = 35;
    public static $RES_ERROR_PERMISSION_DENY = 36;
    public static $RES_ERROR_ALREADY_HAVE_PERMISSION = 37;
    public static $RES_ERROR_PAYMENT_REPEATED  = 38;
    public static $RES_ERROR_PAYMENT_GO_WRONG  = 39;
    public static $RES_ERROR_ORDER_DOES_NOT_EXIST  = 40;
    public static $RES_ERROR_SERVICE_IS_TEMPORARILY_UNAVAILABLE  = 41;
    public static $RES_ERROR_NO_NEED_TO_PAY  = 42;
    public static $RES_ERROR_ORDER_HAS_BEEN_PROCESSED = 43;
    public static $RES_ERROR_INVALID_CALLBACK = 44;
    public static $RES_ERROR_PAYMENT_FAILED = 45;
    public static $RES_SUCCESS_PAYMENT_SUCCESS = 46;



    public static function getError($code)
    {
        switch ($code)
        {
            case self::$RES_ERROR_INVALID_SIGN:
                $error = "invalid sign";
                break;
            case self::$RES_ERROR_UID_NOT_IN_DATABASE:
                $error = "uid not in database";
                break;
            case self::$RES_ERROR_HEADER_OR_UID_NOT_SET:
                $error = "header or uid is not set";
                break;
            case self::$RES_ERROR_MESSAGE_IS_NOT_JSON:
                $error = "message is not json format";
                break;
            case self::$RES_ERROR_LONGITUDE_OR_LATITUDE_IS_NOT_SET:
                $error = "longitude or latitude is not set";
                break;
            case self::$RES_ERROR_LONGITUDE_OR_LATITUDE_IS_NOT_NUMERIC:
                $error = "longitude or latitude is not numeric";
                break;
            case self::$RES_ERROR_HEADER_NOT_FOUND:
                $error = "header not found";
                break;
            case self::$RES_ERROR_UID_NOT_MAC:
                $error = "account not mac";
                break;
            case self::$RES_ERROR_UID_NOT_EXIST:
                $error = "account not exist";
                break;
            case self::$RES_ERROR_UID_NOT_REGISTER:
                $error = "account not register";
                break;
            case self::$RES_ERROR_OPERATE_DATABASE_ERROR:
                $error = "operate database error";
                break;
            case self::$RES_ERROR_UID_ALREADY_EXIST:
                $error = "account already exist";
                break;
            case self::$RES_ERROR_UID_NOT_ENABLE:
                $error = "account not enable";
                break;
            case self::$RES_ERROR_UID_ABNORMAL:
                $error = "account abnormal";
                break;
            case self::$RES_ERROR_NO_UID_ONLINE:
                $error = "no account online";
                break;
            case self::$RES_ERROR_NO_NEED_UPDATE:
                $error = "no need update";
                break;
            case self::$RES_ERROR_NO_LIST_DATA:
                $error = "no list data";
                break;
            case self::$RES_ERROR_SIGNATURE:
                $error = "signature is error";
                break;
            case self::$RES_ERROR_DATA_LEN:
                $error = "data len is error";
                break;
            case self::$RES_ERROR_NO_HAVE_TID:
                $error = "no have tid";
                break;
            case self::$RES_ERROR_NO_HAVE_AID:
                $error = "no have aid";
                break;
            case self::$RES_ERROR_NO_HAVE_IID:
                $error = "no have iid";
                break;
            case self::$RES_ERROR_TIMESTAMP:
                $error = "error timestamp";
                break;
            case self::$RES_ERROR_NO_RECORD:
                $error = "no record";
                break;
            case self::$RES_ERROR_IP_FORBIDDEN:
                $error = "forbidden";
                break;
            case self::$RES_ERROR_INVALID_TOKEN:
                $error = "invalid token";
                break;
            case self::$RES_ERROR_TOKEN_EXPIRE:
                $error = "token has expired";
                break;
            case self::$RES_ERROR_SN_NOT_EXIST:
                $error = "sn not exist";
                break;
            case self::$RES_ERROR_UID_IS_BLACK_LIST:
                $error = "account in blacklist";
                break;
            case self::$RES_ERROR_UID_EXPIRED:
                $error = "account expired";
                break;
            case self::$RES_ERROR_UID_RIGISTERED;
                $error = "already registered";
                break;
            case self::$RES_ERROR_PARAMETER_MISSING;
                $error = 'required parameter missing';
                break;
            case self::$RES_ERROR_INVALID_CARD;
                $error = 'invalid card';
                break;
            case self::$RES_ERROR_PARAMETER:
                $error = 'invalid params';
                break;
            case self::$RES_ERROR_FREE_OF_CHARGE:
                $error = "it's free of charge";
                break;
            case self::$RES_ERROR_PERMISSION_DENY:
                $error = 'permission deny';
                break;
            case self::$RES_ERROR_ALREADY_HAVE_PERMISSION:
                $error = 'already have permission, no need to place an order';
                break;
            case self::$RES_ERROR_PAYMENT_REPEATED:
                $error = 'The order has been paid';
                break;
            case self::$RES_ERROR_PAYMENT_GO_WRONG:
                $error = 'Payment Service temporarily unavailable';
                break;
            case self::$RES_ERROR_ORDER_DOES_NOT_EXIST:
                $error = 'The Order does not exist';
                break;
            case self::$RES_ERROR_SERVICE_IS_TEMPORARILY_UNAVAILABLE:
                $error = 'The service is temporarily unavailable';
                break;
            case self::$RES_ERROR_NO_NEED_TO_PAY:
                $error = 'No need to pay';
                break;
            case self::$RES_ERROR_ORDER_HAS_BEEN_PROCESSED:
                $error = 'The current order has been paid successfully';
                break;
            case self::$RES_ERROR_INVALID_CALLBACK:
                $error = "Invalid callback";
                break;
            case self::$RES_ERROR_PAYMENT_FAILED:
                $error = 'payment failed';
                break;
            case self::$RES_SUCCESS_PAYMENT_SUCCESS:
                $error = 'payment successful';
                break;
            default:
                $error = "other error";
                break;
        }
        return $error;
    }

}