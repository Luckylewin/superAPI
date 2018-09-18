<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/18
 * Time: 10:30
 */

use \Workerman\Worker;
use \Workerman\Connection\AsyncTcpConnection;
use \Workerman\Connection\TcpConnection;


// 自动加载类
require_once __DIR__ . '/../vendor/autoload.php';

$worker = new Worker('tcp://0.0.0.0:10086');

// 启动4个进程对外提供服务
$worker->count = 4;

$connection_to_server = null;

$worker->onConnect = function(TcpConnection $connection)
{
    global $connection_to_server;
    // 链接真实服务器
    $connection_to_server = new AsyncTcpConnection('tcp://207.38.90.29:10086');

    // 接到真实服务器响应信息，返回给客户端
    $connection_to_server->onMessage = function($connection_to_server, $buffer) use ($connection)
    {
        //echo 'from server :' . $buffer . PHP_EOL;
        $connection->send($buffer);
    };

    $connection_to_server->onClose = function($connection_to_server)
    {
        echo "connection closed\n";
    };

    $connection_to_server->onError = function($connection_to_server, $code, $msg)
    {
        echo "Error code:$code msg:$msg\n";
    };

    $connection_to_server->connect();
};

// 收到客户的信息，传送给服务器
$worker->onMessage = function(TcpConnection $connection, $buffer)
{
    //echo "client ip is: " . $connection->getRemoteip() . PHP_EOL;
    /**
     * @var $connection_to_server AsyncTcpConnection
     */
    global $connection_to_server;
    //echo "from client " . $buffer . PHP_EOL;
    $result = $connection_to_server->send($buffer);
    return $connection->send($result);
};

Worker::runAll();