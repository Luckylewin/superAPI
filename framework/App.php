<?php
namespace Breeze;

use Breeze\Http\Middleware;
use Breeze\Http\Request;
use Breeze\Http\Response;
use Workerman\Connection\TcpConnection;
use Illuminate\Database\Capsule\Manager as Capsule;

class App
{
    // 框架初始化
    public static function init()
    {
        $conf = Config::get('database.db');

        // 启动 Illuminate ORM
        $capsule = new Capsule();
        $capsule->addConnection(
            [
                'driver'    => $conf['driver'],
                'host'      => $conf['host'],
                'database'  => $conf['database'],
                'username'  => $conf['username'],
                'password'  => $conf['password'],
                'charset'   => $conf['charset'],
                'collation' => $conf['collation'],
                'port'      => $conf['port']
            ]
        );

        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        //echo "框架初始化",PHP_EOL;
    }

    // 框架运行

    /**
     * @param TcpConnection $connection
     */
    public static function run(TcpConnection $connection)
    {
        try {
            // 配置项
            $conf = Config::get('app');
            // 注册中间件
            $registeredMiddleware = Config::get('middleware.global');
            // Request 对象
            $request = new Request();
            // 用户iP
            $request->setIP($connection->getRemoteIp());
            $format = $request->server('HTTP_ACCEPT');

            // 兼容json串路由
            if ($request->server()->REQUEST_URI === '/') {
                $rawData = json_decode($request->rawData()->scalar);

                if (isset($rawData->header)) {
                    $request->redirect('/' . $rawData->header);
                }
            }

            $result = Middleware::run($registeredMiddleware, $request);

            if ($result instanceof Request) {
                // 分发路由
                $result = Route::dispatch($result);
            }

        } catch (\Exception $e) {
            echo $e->getMessage(), PHP_EOL;
        } finally {

            $response = Response::build($result, $conf, $format);
            $connection->send($response);
        }
    }

    // 初始化类
    public static function register()
    {

    }
}