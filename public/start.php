<?php
require_once '../vendor/autoload.php';

use Workerman\Connection\TcpConnection;
use Workerman\Worker;
use Breeze\App;

Worker::$stdoutFile = '../storage/logs/error.log';
Worker::$logFile = '../storage/logs/workerman.log';

$http_worker = new Worker('http://0.0.0.0:12389');
$http_worker->count = 16;
$http_worker->user = 'nginx';

$http_worker->onWorkerStart = function($http_worker)
{
    // 加载配置文件
    require_once __DIR__ . '/../bootstrap/boot.php';
    // 初始化应用
    App::init();
};

$http_worker->onMessage = function(TcpConnection $connection, $data)
{
    App::run($connection);
};


define('APP_ROOT', dirname(dirname(__FILE__)) . '/');
define('LOG_PATH', dirname(dirname(__FILE__)) . '/storage/logs/');

define('CHARGE_MODE', 0);

Worker::runAll();
