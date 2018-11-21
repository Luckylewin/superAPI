<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/3
 * Time: 11:04
 */

namespace App\Controller;

use App\Components\http\Formatter;
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
    /**
     * 获取访问令牌
     * @return string
     */
    public function getClientToken()
    {
        $data = $this->request->post('data');
        $time = $data['time'];
        $sign = $data['sign'];

        $authService = new authService($this->request);
        $token = $authService->getClientToken($time, $sign);
        if ($token['status'] === false) {
            return Formatter::response($token['code']);
        }

        return $token['data'];
    }

    // 升级APP
    public function getNewApp(): array
    {
        if ($this->request->isGet) {
            $ver = $this->request->get('ver', 0);
            $type = $this->request->get('type');
        } else {
            $ver = $this->request->post('ver', 0);
            $type = $this->request->post('type');
        }

        $appService = new appService($this->request);
        $app = $appService->updateApp($type, $ver);

        if ($app['status'] === false) {
            return Formatter::response($app['code']);
        }

        return Formatter::success($app['data']);
    }


    // 升级固件
    public function getFirmware(): array
    {
        $orderId       = $this->request->post('order_id', null);
        $clientVersion = $this->request->post('version', 0);

        $firmwareService = new firmwareService($this->request);
        $firmware = $firmwareService->getFirmware($orderId, $clientVersion);
        if ($firmware['status'] === false) {
            return Formatter::response($firmware['code']);
        }

        return Formatter::success($firmware['data']);
    }

    // 升级安卓固件
    public function getAndroidFirmware(): array
    {
        $orderId = $this->request->post('order_id', null);
        $clientVersion = $this->request->post('version', 0);
        $firmwareService = new firmwareService($this->request);
        $firmware = $firmwareService->getFirmware($orderId, $clientVersion, 'android');
        if ($firmware['status'] === false) {
            return Formatter::response($firmware['code']);
        }

        return Formatter::success($firmware['data']);
    }

    // APP市场
    public function getAppMarket(): array
    {
        $sign = $this->request->post('sign');
        $time = $this->request->post('time');
        $scheme = $this->request->post('scheme', 'all');
        $page  = $this->request->post('page', 1);
        $limit = $this->request->post('per_page', 10);

        $appService = new appService($this->request);
        $market = $appService->getAppMarket($sign, $time, $scheme, $page, $limit);

        if ($market['status'] === false) {
            return Formatter::response($market['code']);
        }

        return Formatter::success($market['data']);
    }

    // 取具体的APP数据
    public function getConcreteApp(): array
    {
        $sign  = $this->request->post('sign');
        $time  = $this->request->post('time');
        $appId = $this->request->post('appid');

        $appService = new appService($this->request);
        $app        = $appService->getConcreteApp($appId, $time, $sign);

        if ($app['status'] === false) {
            return Formatter::response($app['code']);
        }

        return Formatter::success($app['data']);
    }

    // 取直播列表
    public function getOttList()
    {
        $scheme  = $this->request->post('scheme', 'ALL');
        $country = $this->request->post('country', '');
        $type    = $this->request->post('type', '');
        $genre   = $country ? $country : $type;
        $access_key = $this->request->post('access_key', '');
        $version = str_replace('_', '.', $this->request->post('ver', 0));
        $format  = strtoupper($this->request->post('format', 'XML'));

        $ottService = new ottService($this->request);
        $list = $ottService->getList($genre, $version, $scheme, $format, $access_key);
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
     * @return array
     */
    public function getMainClass(): array
    {
        $ottService = new ottService($this->request);
        $data = $ottService->getMainClass();
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    // 获取正则
    public function getRegex(): array
    {
        $version = $this->request->post('version', 0);
        $ottService = new ottService($this->request);
        $data = $ottService->getRegex($version);
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
        $prev = $this->request->post('prev', 6);
        $hour = $this->request->post('hour', 24);
        $language = $this->request->post('lang', 'en');
        $timezone = $this->request->post('timezone', '+8');

        $ottService = new ottService($this->request);
        $data = $ottService->getRecentEvent($language, $timezone, $hour, $prev);
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    // 卡拉ok列表
    public function getKaraokeList(): array
    {
        $searcher = new KaraokeSearcher();
        $searcher->name = $this->request->post('name', false);
        $searcher->lang = $this->request->post('lang', false);
        $searcher->tags = $this->request->post('tags', false);
        $searcher->sort = $this->request->post('sort', 'update_time:asc');
        $searcher->page = $this->request->post('page', '1');
        $searcher->perPage = $this->request->post('perPage', 10);

        $iptvService = new iptvService($this->request);
        $data = $iptvService->getKaraokeList($searcher);
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    /**
     * 播放卡拉ok
     * @return array
     */
    public function getKaraoke(): array
    {
        $url = $this->request->post('url', null);
        $iptvService = new iptvService($this->request);
        $data = $iptvService->getKaraoke($url);
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    // 续费
    public function renew(): array
    {
        $cardSecret  = $this->request->post('card_secret', null);
        $iptvService = new chargeService($this->request);
        $data = $iptvService->renew($cardSecret);
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    // 注册
    public function register()
    {
        $sign = $this->request->post('sign');
        $sn   = $this->request->post('sn');

        $userService = new userService($this->request);
        $data = $userService->signup($sign, $sn);
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    // 获取过期时间
    public function getExpireTime(): array
    {
        $userService = new userService($this->request);
        $data = $userService->getMacExpireTime();
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    // 获取帐号信息
    public function getAccountInfo(): array
    {
        $from = $this->request->post('from', 'box');
        $userService = new userService($this->request);

        if ($from == 'box') {
            $data = $userService->getInfoFromBox();
        } else {
            $data = $userService->getInfoFromPhone();
        }

        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    //取服务器列表
    public function getServerList(): array
    {
        $data = [
            'version' => '20180904',
            'server' => ['192.200.112.162']
        ];

        return Formatter::success($data);
    }

    //获取服务器时间
    public function getServerTime(): array
    {
        return ['time' => time()];
    }

    // 取预告列表
    public function getEPG(): array
    {
        $version = $this->request->post('version', 0);
        $genre = $this->request->post('genre', false);

        $ottService = new ottService($this->request);
        $data = $ottService->getEPG($genre, $version);
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    // 下单接口
    public function ottCharge()
    {
        // 查找分类价格
        $genre = $this->request->post('genre');
        $type  = $this->request->post('type', 1);
        $timestamp = $this->request->post('timestamp');
        $sign  = $this->request->post('sign');

        $data = (new chargeService($this->request))->openService($genre, $type, $sign, $timestamp);
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    public function getOttPriceList(): array
    {
        $lang = $this->request->post('lang', 'en_US');
        $data = (new chargeService($this->request))->getOttPriceList($lang);
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }



    public function getBootPic(): array
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
        $day = $this->request->post('day', 7);
        $language = $this->request->post('lang', 'en');
        $timezone = $this->request->post('timezone', '+8');

        $ottService = new ottService($this->request);
        $data = $ottService->getMajorEvent($day, $language, $timezone);
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    // 获取预告列表
    public function getParadeList()
    {
        $timezone = $this->request->post('timezone', '+8');
        $name = $this->request->post('name',null);
        $day = $this->request->post('day', 1);

        $ottService = new ottService($this->request);
        $data = $ottService->getParadeList($name, $day, $timezone);

        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    // 获取频道图标
    public function getChannelIcon()
    {
        $name = $this->request->post('name');
        $ottService = new ottService($this->request);
        $data = $ottService->getChannelIcon($name);
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    // 获取当个分类详情
    public function getGenre(): array
    {
        $name = $this->request->post('name');
        $ottService = new ottService($this->request);
        $data = $ottService->getGenre($name);
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    // 获取分类使用状态
    public function getGenreUsageInfo()
    {
        $genre = $this->request->post('genre');
        $access_key = $this->request->post('access_key', '');
        $ottService = new ottService($this->request);
        $data = $ottService->getGenreUsageInfo($genre, $access_key);

        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    // 获取分类价目表
    public function getGenrePrice(): array
    {
        $genre = $this->request->post('genre');
        $lang  = $this->request->post('lang', 'en_US');

        $ottService = new ottService($this->request);
        $data = $ottService->getGenrePrice($genre, $lang);

        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    // 使用激活卡对分类进行激活操作
    public function activateGenre(): array
    {
        $genre      = $this->request->post('genre');
        $cardSecret = $this->request->post('card_secret', null);
        $sign       = $this->request->post('sign');
        $timestamp  = $this->request->post('timestamp');


        $ottService = new ottService($this->request);
        $data = $ottService->activateGenre($genre, $cardSecret, $sign, $timestamp);
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    // 获取隐藏内容隐藏状态
    public function getLockStatus(): array
    {
        $ottService = new ottService($this->request);
        $data = $ottService->getLockStatus();
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }

    // 解锁隐藏内容
    public function relieveLock(): array
    {
        $ottService = new ottService($this->request);
        $data = $ottService->relieveLock();
        if ($data['status'] === false) {
            return Formatter::response($data['code']);
        }

        return Formatter::success($data['data']);
    }
}