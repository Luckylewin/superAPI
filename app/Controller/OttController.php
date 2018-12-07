<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/3
 * Time: 11:04
 */

namespace App\Controller;

use App\Components\http\Formatter;
use App\Exceptions\ErrorCode;
use App\Models\KaraokeSearcher;
use App\Service\appService;
use App\Service\authService;
use App\Service\firmwareService;
use App\Service\iptvService;
use App\Service\ottService;
use App\Service\chargeService;
use App\Service\userService;
use Breeze\Http\Response;

class OttController extends BaseController
{
    public function setError($errorCode)
    {
        $this->error = ErrorCode::getError($errorCode);
    }

    /**
     * 获取访问令牌
     * @return string
     */
    public function getClientToken(): string
    {
        $data = $this->request->post('data');
        $time = $data['time'];
        $sign = $data['sign'];

        $authService = new authService($this->request);
        $token = $authService->getClientToken($time, $sign);
        if ($token['status'] === false) {
            return $this->fail($token['code']);
        }

        Formatter::format(Formatter::JSON);
        return $token['data'];
    }

    // 升级APP
    public function getNewApp(): string
    {
        try {
            if ($this->request->isGet) {
                $ver = $this->request->get('ver', 0);
                $type = $this->request->get('type');
            } else {
                $ver  = $this->post('ver', 0);
                $type = $this->post('type');
            }
        } catch (\Exception $e) {
            return $this->fail($e->getCode());
        }

        $appService = new appService($this->request);
        $app        = $appService->updateApp($type, $ver);

        if ($app['status'] === false) {
            return $this->fail($app['code']);
        }

        return $this->success($app['data']);
    }


    // 升级固件
    public function getFirmware(): string
    {
        try {
            $orderId       = $this->post('order_id', null);
            $clientVersion = $this->post('version', 0);
        } catch (\Exception $e) {
            return $this->fail($e->getCode());
        }

        $firmwareService = new firmwareService($this->request);
        $firmware = $firmwareService->getFirmware($orderId, $clientVersion);
        if ($firmware['status'] === false) {
            return $this->fail($firmware['code']);
        }

        return $this->success($firmware['data']);
    }

    // 升级安卓固件
    public function getAndroidFirmware()
    {
        try {
            $orderId = $this->post('order_id', null);
            $clientVersion = $this->post('version', 0);
        } catch (\Exception $e) {
            return $this->fail($e->getCode());
        }

        $firmwareService = new firmwareService($this->request);
        $firmware = $firmwareService->getFirmware($orderId, $clientVersion, 'android');
        if ($firmware['status'] === false) {
            return $this->fail($firmware['code']);
        }

        return $this->success($firmware['data']);
    }

    // APP市场
    public function getAppMarket(): string
    {
        try {
            $sign = $this->post('sign');
            $time = $this->post('time');
            $scheme = $this->post('scheme', 'all');
            $page  = $this->post('page', 1);
            $limit = $this->post('per_page', 10);
        } catch (\Exception $e) {

            return $this->fail($e->getCode());
        }

        $appService = new appService($this->request);
        $market = $appService->getAppMarket($sign, $time, $scheme, $page, $limit);

        if ($market['status'] === false) {
            return $this->fail($market['code']);
        }

        return $this->success($market['data']);
    }

    // 取具体的APP数据
    public function getConcreteApp(): string
    {
        try {
            $sign  = $this->post('sign');
            $time  = $this->post('time');
            $appId = $this->post('appid');
        } catch (\Exception $e) {
            return $this->fail($e->getCode());
        }

        $appService = new appService($this->request);
        $app        = $appService->getConcreteApp($appId, $time, $sign);

        if ($app['status'] === false) {
            return $this->fail($app['code']);
        }

        return $this->success($app['data']);
    }

    // 取直播列表
    public function getOttList()
    {
        try {
            $scheme  = $this->post('scheme', 'ALL');
            $country = $this->post('country', '');
            $type    = $this->post('type', '');
            $genre   = $country ? $country : $type;
            $access_key = $this->post('access_key', '');
            $version = str_replace('_', '.', $this->post('ver', 0));
            $format  = strtoupper($this->post('format', 'XML'));
        } catch (\Exception $e) {
            return $this->fail($e->getCode());
        }

        $ottService = new ottService($this->request);
        $list = $ottService->getList($genre, $version, $scheme, $format, $access_key);
        if ($list['status'] === false) {
            return $this->fail($list['code']);
        }

        if ($format == 'json') {
            return $this->success($list['data']);
        } else {
            Response::format(Response::XML);
            return $list['data'];
        }

    }

    /**
     * 获取主要分类
     * @return string
     */
    public function getMainClass(): string
    {
        $ottService = new ottService($this->request);
        $data = $ottService->getMainClass();
        if ($data['status'] === false) {
            return $this->fail($data['code']);
        }

        return $this->success($data['data']);
    }

    // 获取正则
    public function getRegex(): string
    {
        try {
            $version = $this->post('version', 0);
        } catch (\Exception $e) {

            return $this->fail($e->getCode());
        }

        $ottService = new ottService($this->request);
        $data = $ottService->getRegex($version);
        if ($data['status'] === false) {
            return $this->fail($data['code']);
        }

        return $this->success($data['data']);
    }

    // 轮播图
    public function getOttBanners()
    {
        $ottService = new ottService($this->request);
        $data = $ottService->getBanners();
        if ($data['status'] === false) {
            return $this->fail($data['code']);
        }

        return $this->success($data['data']);
    }

    // 轮播图
    public function getOttRecommend()
    {
        $ottService = new ottService($this->request);
        $data = $ottService->getRecommend();
        if ($data['status'] === false) {
            return $this->fail($data['code']);
        }

        return $this->success($data['data']);
    }

    // 获取未来N小时的比赛
    public function getRecentEvent()
    {
        try {
            $prev = $this->post('prev', 6);
            $hour = $this->post('hour', 24);
            $language = $this->post('lang', 'en');
            $timezone = $this->post('timezone', '+8');
        } catch (\Exception $e) {
            return $this->fail($e->getCode());
        }

        $ottService = new ottService($this->request);
        $data = $ottService->getRecentEvent($language, $timezone, $hour, $prev);
        if ($data['status'] === false) {
            return $this->fail($data['code']);
        }

        return $this->success($data['data']);
    }

    // 卡拉ok列表
    public function getKaraokeList(): string
    {
        $searcher = new KaraokeSearcher();
        try {
            $searcher->name = $this->post('name', false);
            $searcher->lang = $this->post('lang', false);
            $searcher->tags = $this->post('tags', false);
            $searcher->sort = $this->post('sort', 'update_time:asc');
            $searcher->page = $this->post('page', '1');
            $searcher->perPage = $this->post('perPage', 10);
        } catch (\Exception $e) {
            return $this->fail($e->getCode());
        }

        $iptvService = new iptvService($this->request);
        $data = $iptvService->getKaraokeList($searcher);

        if ($data['status'] === false) {
            return $this->fail($data['code']);
        }

        return $this->success($data['data']);
    }

    /**
     * 播放卡拉ok
     * @return string
     */
    public function getKaraoke(): string
    {
        try {
            $url = $this->post('url', null);
        } catch (\Exception $e) {
            return $this->fail($e->getCode());
        }

        $iptvService = new iptvService($this->request);
        $data = $iptvService->getKaraoke($url);
        if ($data['status'] === false) {
            return $this->fail($data['code']);
        }

        return $this->success($data['data']);
    }

    // 续费
    public function renew(): string
    {
        try {
            $cardSecret = $this->post('card_secret', null);
        } catch (\Exception $e) {
            return $this->fail($e->getCode());
        }

        $iptvService = new chargeService($this->request);
        $data = $iptvService->renew($cardSecret);
        if ($data['status'] === false) {
            return $this->fail($data['code']);
        }

        return $this->success($data['data']);
    }

    // 注册
    public function register()
    {
        try {
            $sign = $this->post('sign');
            $sn   = $this->post('sn');
        } catch (\Exception $e) {
            return $this->fail($e->getCode());
        }

        $userService = new userService($this->request);
        $data = $userService->signup($sign, $sn);
        if ($data['status'] === false) {
            return $this->fail($data['code']);
        }

        return $this->success($data['data']);
    }

    // 获取过期时间
    public function getExpireTime(): string
    {
        $userService = new userService($this->request);
        $data = $userService->getMacExpireTime();
        if ($data['status'] === false) {
            return $this->fail($data['code']);
        }

        return $this->success($data['data']);
    }

    // 获取帐号信息
    public function getAccountInfo(): string
    {
        try {
            $from = $this->post('from', 'box');
        } catch (\Exception $e) {
            return $this->fail($e->getCode());
        }

        $userService = new userService($this->request);

        if ($from == 'box') {
            $data = $userService->getInfoFromBox();
        } else {
            $data = $userService->getInfoFromPhone();
        }

        if ($data['status'] === false) {
            return $this->fail($data['code']);
        }

        return $this->success($data['data']);
    }

    //取服务器列表
    public function getServerList(): string
    {
        $data = [
            'version' => '20180904',
            'server' => ['192.200.112.162']
        ];

        return $this->success($data);
    }

    //获取服务器时间
    public function getServerTime(): array
    {
        return ['time' => time()];
    }

    // 取预告列表
    public function getEPG()
    {
        try {
            $version = $this->post('version', 0);
            $genre = $this->post('genre', false);
        } catch (\Exception $e) {
            return $this->fail($e->getCode());
        }

        $ottService = new ottService($this->request);
        $data = $ottService->getEPG($genre, $version);
        if ($data['status'] === false) {
            return $this->fail($data['code']);
        }

        return $this->success($data['data']);
    }

    // 下单接口
    public function ottCharge()
    {
        try {
            $genre = $this->post('genre');
            $type  = $this->post('type', 1);
            $timestamp = $this->post('timestamp');
            $sign  = $this->post('sign');
        } catch (\Exception $e) {
            return $this->fail($e->getCode());
        }

        $data = (new chargeService($this->request))->openService($genre, $type, $sign, $timestamp);
        if ($data['status'] === false) {
            return $this->fail($data['code']);
        }

        return $this->success($data['data']);
    }

    public function getOttPriceList(): string
    {
        try {
            $lang = $this->post('lang', 'en_US');
        } catch (\Exception $e) {
            return $this->fail($e->getCode());
        }

        $data = (new chargeService($this->request))->getOttPriceList($lang);
        if ($data['status'] === false) {
            return $this->fail($data['code']);
        }

        return $this->success($data['data']);
    }



    public function getBootPic(): string
    {
        $data = (new appService($this->request))->getBootPic();

        if ($data['status'] === false) {
            return $this->fail($data['code']);
        }

        return $this->success($data['data']);
    }


    // 获取主要赛事
    public function getMajorEvent()
    {
        try {
            $day = $this->post('day', 7);
            $language = $this->post('lang', 'en');
            $timezone = $this->post('timezone', '+8');
        } catch (\Exception $e) {
            return $this->fail($e->getCode());
        }

        $ottService = new ottService($this->request);
        $data = $ottService->getMajorEvent($day, $language, $timezone);
        if ($data['status'] === false) {
            return $this->fail($data['code']);
        }

        return $this->success($data['data']);
    }

    // 获取预告列表
    public function getParadeList()
    {
        try {
            $timezone = $this->post('timezone', '+8');
            $name = $this->post('name',null);
            $day = $this->post('day', 1);
        } catch (\Exception $e) {

            return $this->fail($e->getCode());
        }

        $ottService = new ottService($this->request);
        $data = $ottService->getParadeList($name, $day, $timezone);

        if ($data['status'] === false) {
            return $this->fail($data['code']);
        }

        return $this->success($data['data']);
    }

    // 获取频道图标
    public function getChannelIcon()
    {
        try {
            $name = $this->post('name');
        } catch (\Exception $e) {
            return $this->fail($e->getCode());
        }

        $ottService = new ottService($this->request);
        $data = $ottService->getChannelIcon($name);
        if ($data['status'] === false) {
            return $this->fail($data['code']);
        }

        return $this->success($data['data']);
    }

    // 获取当个分类详情
    public function getGenre(): string
    {
        try {
            $name = $this->post('name');
        } catch (\Exception $e) {
            return $this->fail($e->getCode());
        }
        $ottService = new ottService($this->request);
        $data = $ottService->getGenre($name);
        if ($data['status'] === false) {
            return $this->fail($data['code']);
        }

        return $this->success($data['data']);
    }

    // 获取分类使用状态
    public function getGenreUsageInfo()
    {
        try {
            $genre = $this->post('genre');
            $access_key = $this->post('access_key', '');
        } catch (\Exception $e) {
            return $this->fail($e->getCode());
        }

        $ottService = new ottService($this->request);
        $data = $ottService->getGenreUsageInfo($genre, $access_key);

        if ($data['status'] === false) {
            return $this->fail($data['code']);
        }

        return $this->success($data['data']);
    }

    // 获取分类价目表
    public function getGenrePrice(): string
    {
        try {
            $genre = $this->post('genre');
            $lang  = $this->post('lang', 'en_US');
        }catch (\Exception $e) {
            return $this->fail($e->getCode());
        }

        $ottService = new ottService($this->request);
        $data = $ottService->getGenrePrice($genre, $lang);

        if ($data['status'] === false) {
            return $this->fail($data['code']);
        }

        return $this->success($data['data']);
    }

    // 使用激活卡对分类进行激活操作
    public function activateGenre(): string
    {
        try {
            $genre      = $this->post('genre');
            $cardSecret = $this->post('card_secret', '');
            $sign       = $this->post('sign');
            $timestamp  = $this->post('timestamp');
        } catch (\Exception $e) {
            return $this->fail($e->getCode());
        }

        $ottService = new ottService($this->request);
        $data = $ottService->activateGenre($genre, $cardSecret, $sign, $timestamp);
        if ($data['status'] === false) {
            return $this->fail($data['code']);
        }

        return $this->success($data['data']);
    }

    // 获取隐藏内容隐藏状态
    public function getLockStatus(): string
    {
        $ottService = new ottService($this->request);
        $data = $ottService->getLockStatus();
        if ($data['status'] === false) {
            return $this->fail($data['code']);
        }

        return $this->success($data['data']);
    }

    // 解锁隐藏内容
    public function relieveLock(): string
    {
        $ottService = new ottService($this->request);
        $data = $ottService->relieveLock();
        if ($data['status'] === false) {
            return $this->fail($data['code']);
        }

        return $this->success($data['data']);
    }
}