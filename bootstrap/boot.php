<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/8/31
 * Time: 17:43
 */
use Breeze\Config;

// 加载应用配置
$configFiles = glob('../config/*.php');
foreach ($configFiles as $file) {
    Config::load(basename($file, '.php'), (require_once $file));
}

// 加载路由
require_once '../routes/app.php';