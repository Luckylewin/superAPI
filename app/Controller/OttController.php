<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/3
 * Time: 11:04
 */

namespace App\Controller;

use App\Components\http\Formatter;
use App\Components\pay\DokyPay;
use App\Service\appService;
use App\Service\authService;
use App\Service\firmwareService;
use App\Service\iptvService;
use App\Service\ottService;
use App\Service\chargeService;
use App\Service\userService;
use Breeze\Http\Request;
use Breeze\Http\Response;

class OttController extends BaseController
{

    public function index(Request $request)
    {
        Response::format(Response::JSON);
        return ['status' => true];
    }

    /**
     * 获取访问令牌
     * @return string
     */
    public function getClientToken()
    {
        $authService = new authService($this->request);
        $token = $authService->getClientToken();
        if ($token['status'] === false) {
            return Formatter::response($token['code']);
        }

        return $token['data'];
    }

    // 升级APP
    public function getNewApp()
    {
        $appService = new appService($this->request);
        $app = $appService->updateApp();

        if ($app['status'] === false) {
            return Formatter::response($app['code']);
        }

        return Formatter::success($app['data']);
    }


    // 升级固件
    public function getFirmware()
    {
        $firmwareService = new firmwareService($this->request);
        $firmware = $firmwareService->getFirmware();
        if ($firmware['status'] === false) {
            return Formatter::response($firmware['code']);
        }

        return Formatter::success($firmware['data']);
    }

    // 升级安卓固件
    public function getAndroidFirmware()
    {
        $firmwareService = new firmwareService($this->request);
        $firmware = $firmwareService->getFirmware('android');
        if ($firmware['status'] === false) {
            return Formatter::response($firmware['code']);
        }

        return Formatter::success($firmware['data']);
    }

    // APP市场
    public function getAppMarket()
    {
        $appService = new appService($this->request);
        $market = $appService->getAppMarket();

        if ($market['status'] === false) {
            return Formatter::response($market['code']);
        }

        return Formatter::success($market['data']);
    }

    // 取具体的APP数据
    public function getConcreteApp()
    {
        $appService = new appService($this->request);
        $app = $appService->getConcreteApp();

        if ($app['status'] === false) {
            return Formatter::response($app['code']);
        }

        return Formatter::success($app['data']);
    }

    // 取直播列表
    public function getOttList()
    {
        $ottService = new ottService($this->request);
        $list = $ottService->getList();
        if ($list['status'] === false) {
            return Formatter::response($list['code']);
        }

        $format = $this->request->post('format') ?? 'xml';
        if ($format == 'json') {
            return Formatter::success($list['data']);
        } else {
            Response::format(Response::XML);
            return $list['data'];
        }

    }

    /**
     * 获取主要分类
     * @return string
     */
    public function getMainClass()
    {
        $ottService = new ottService($this->request);
        $data = $ottService->getMainClass();
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    // 获取正则
    public function getRegex()
    {
        $ottService = new ottService($this->request);
        $data = $ottService->getRegex();
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    // 轮播图
    public function getOttBanners()
    {
        $ottService = new ottService($this->request);
        $data = $ottService->getBanners();
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    // 轮播图
    public function getOttRecommend()
    {
        $ottService = new ottService($this->request);
        $data = $ottService->getRecommend();
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    // 获取未来N小时的比赛
    public function getRecentEvent()
    {
        $ottService = new ottService($this->request);
        $data = $ottService->getRecentEvent();
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    // 卡拉ok列表
    public function getKaraokeList()
    {
        $iptvService = new iptvService($this->request);
        $data = $iptvService->getKaraokeList();
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    /**
     * 播放卡拉ok
     * @return string
     */
    public function getKaraoke()
    {
        $iptvService = new iptvService($this->request);
        $data = $iptvService->getKaraoke();
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    // 续费
    public function renew()
    {
        $iptvService = new chargeService($this->request);
        $data = $iptvService->renew();
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    // 注册
    public function register()
    {
        $userService = new userService($this->request);
        $data = $userService->signup();
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    // 获取过期时间
    public function getExpireTime()
    {
        $userService = new userService($this->request);
        $data = $userService->getMacExpireTime();
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    // 获取帐号信息
    public function getAccountInfo()
    {
        $userService = new userService($this->request);
        $data = $userService->getInfo();
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    //取服务器列表
    public function getServerList()
    {
        $data = [
            'version' => '20180904',
            'server' => ['192.200.112.162']
        ];

        return Formatter::success($data);
    }

    //获取服务器时间
    public function getServerTime()
    {
        return ['time' => time()];
    }

    // 取预告列表
    public function getEPG()
    {
        $ottService = new ottService($this->request);
        $data = $ottService->getEPG();
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    // 下单接口
    public function ottCharge()
    {
        $data = (new chargeService($this->request))->openService();
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    public function getOttPriceList()
    {
        $data = (new chargeService($this->request))->getOttPriceList();
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }



    public function getBootPic()
    {
        $data = (new appService($this->request))->getBootPic();

        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }


    // 获取主要赛事
    public function getMajorEvent()
    {
        $ottService = new ottService($this->request);
        $data = $ottService->getMajorEvent();
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    // 获取预告列表
    public function getParadeList()
    {
        $ottService = new ottService($this->request);
        $data = $ottService->getParadeList();
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    // 获取频道图标
    public function getChannelIcon()
    {
        $ottService = new ottService($this->request);
        $data = $ottService->getChannelIcon();
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    // 获取当个分类详情
    public function getGenre()
    {
        $ottService = new ottService($this->request);
        $data = $ottService->getGenre();
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    // 获取分类使用状态
    public function getGenreUsageInfo()
    {
        $ottService = new ottService($this->request);
        $data = $ottService->getGenreUsageInfo();
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    // 获取分类价目表
    public function getGenrePrice()
    {
        $ottService = new ottService($this->request);
        $data = $ottService->getGenrePrice();
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    // 使用激活卡对分类进行激活操作
    public function activateGenre()
    {
        $ottService = new ottService($this->request);
        $data = $ottService->activateGenre();
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    // 获取隐藏内容隐藏状态
    public function getLockStatus()
    {
        $ottService = new ottService($this->request);
        $data = $ottService->getLockStatus();
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    // 解锁隐藏内容
    public function relieveLock()
    {
        $ottService = new ottService($this->request);
        $data = $ottService->relieveLock();
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    public function test()
    {
        try {
            (new DokyPay())->queryOrder('201809071336533230015062');
        } catch (\Exception $e) {

        }
    }

}