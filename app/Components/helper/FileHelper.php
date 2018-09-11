<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/10
 * Time: 15:34
 */

namespace App\Components\helper;


class FileHelper
{
    public static function exist($path)
    {
        return file_exists($path);
    }

    public static function createDirectory($filePath)
    {
       if (static::exist($filePath) == false) {
           mkdir($filePath,  0777, true);
           chmod($filePath,0777);
       }
    }

    public static function createFile($filePath)
    {
        if (file_exists($filePath) == false) {
            $dir = dirname($filePath);
            if (is_dir($dir) == false) {
                self::createDirectory($dir);
            }
            touch($filePath);
            chmod($filePath, '777');
        }
        chmod($filePath, 0777);
    }

}