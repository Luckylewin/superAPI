<?php
/**
 * Created by PhpStorm.
 * User: sz
 * Date: 2017/10/18
 * Time: 17:21
 */

namespace App\Service;

use App\Components\cache\Redis;
use App\Components\encrypt\AES;
use App\Components\helper\ArrayHelper;
use App\Components\helper\Func;
use App\Components\http\Formatter;
use App\Exceptions\ErrorCode;
use Illuminate\Database\Capsule\Manager as Capsule;

class ottService extends common
{

    public $source = 'skysport';

    /**
     * 首页推荐
     * @return array
     * @throws
     */
    public function getRecommend(): array
    {
        $version = $this->post('version', 0);
        
        $cacheKey = 'OTT_RECOMMEND';
        $redisDB = Redis::$REDIS_PROTOCOL;

        $recommends = $this->getDataFromCache($cacheKey, $redisDB);

        if (empty($recommends)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        if ($version == $recommends['version']) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_NEED_UPDATE];
        }

        array_walk($recommends['data'], function(&$v) {
            if (strpos($v['image'], 'http') === false) {
                $v['image'] = Func::getAccessUrl($this->uid, $v['image'],  86400);
            }
        });

        return ['status' => true, 'data' => $recommends];
    }

    /**
     * 首页banner数据
     * @return array
     */
    public function getBanners(): array
    {
        $cacheKey = 'OTT_BANNERS';
        $redisDB = Redis::$REDIS_PROTOCOL;

        $banners = $this->getDataFromCache($cacheKey, $redisDB);

        if (empty($banners)) {
            $this->stdout("没有数据", 'ERROR');
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        array_walk($banners['data'], function(&$v){
            if (strpos($v['image'], 'http') === false) {
                $v['image'] = Func::getAccessUrl($this->uid, $v['image'],  86400);
            }
            if (strpos($v['image_big'], 'http') === false) {
                $v['image_big'] = Func::getAccessUrl($this->uid, $v['image_big'],  86400);
            }
            if (strpos($v['channels']['image'], 'http') === false) {
                $v['channels']['image'] = Func::getAccessUrl($this->uid, $v['channels']['image'],  86400);
            }
        });

        return ['status' => true, 'data' => $banners];
    }

    /**
     * @param $language
     * @param $timezone
     * @param $hour
     * @param $prev
     * @return array
     */
    public function getRecentEvent($language, $timezone, $hour, $prev): array
    {
        $language = strtolower($language);
        $during = $this->getDuringHour($timezone, $hour);

        $during['start'] -= 3600 * $prev;
        $data = $this->majorEventForHour($language, $timezone, $during['start'], $during['end']);

        if (empty($data)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        return ['status' => true, 'data' => $data];
    }

    /**
     * 获取主要赛事
     * @param $day
     * @param $language
     * @param $timezone
     * @return array
     */
    public function getMajorEvent($day, $language, $timezone): array
    {
        $language = strtolower($language);
        $during = $this->getDuring($timezone, $day);
        $responseDataList = [];
        foreach ($during as $date) {
            $data = $this->majorEventForDay($language, $timezone, $date['start'], $date['end']);
            if ($data) {
                $responseDataList[] = $data;
            }
        }

        //按天进行分组
        if (empty($responseDataList)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        return ['status' => true, 'data' => $responseDataList];
    }


    /**
     * 获取主要赛事列表
     * @param $language string 语言 目前支持zh_cn en_us
     * @param $timezone integer string
     * @param $start_time integer
     * @param $end_time integer
     * @return bool
     */
    protected function majorEventForDay($language, $timezone, $start_time, $end_time)
    {
        //查数据库
        $data = $this->getMajorEventByTime($start_time, $end_time);

        if (empty($data))  return false;

        $responseData = $this->_organizeData($data, $timezone, $start_time, $language);

        return $responseData;
    }

    /**
     * 组织数据
     * @param $data
     * @param $timezone
     * @param $start_time
     * @param $language
     * @return bool|mixed
     */
    private function _organizeData($data, $timezone, $start_time, $language)
    {
        $responseData = $this->_setBaseTimeInfo($timezone, $start_time);

        foreach ($data as $event) {

            $event_info = json_decode($event['live_match']);

            // 判断时间 如果大于比赛时间6小时则丢弃
            if (time() - $event_info->event_time > 21600)  continue;

            $event_info = $this->_fillEventInfo($event ,$event_info, $responseData['date'], $language);

            //频道信息
            $channel_group = $channel_list = [];
            $match_data = json_decode($event['match_data']);

            //按语言进行分组
            if (!empty($match_data)) {
                foreach ($match_data as $channel) {
                    $channel_group[$channel->channel_language][] = $channel;
                }
            }
            if (!empty($channel_group)) {
                $channel_list = $this->_fillCountry($channel_group);
            }
            $event_info['channel_list'] = $channel_list;
            $responseData['event_list'][] = $event_info;

        }

        if (!isset($responseData['event_list']))  return false;

        return $responseData;
    }

    /**
     * 设置时间
     * @param $timezone
     * @param $start_time
     * @return mixed
     */
    private function _setBaseTimeInfo($timezone, $start_time)
    {
        $offset = $timezone * 3600;

        $weekArray = ["星期日","星期一","星期二","星期三","星期四","星期五","星期六"];

        $responseData['timezone'] = $timezone;
        $responseData['date'] = date('Y-m-d', $start_time + $offset);
        $responseData['weekday'] = $weekArray[date('w', $start_time + $offset)];
        $responseData['date_format'] = date('M dS l', $start_time + $offset);
        $responseData['timestamp'] = $start_time;

        return $responseData;
    }

    /**
     * 填充比赛信息
     * @param $majorEventGenre
     * @param $event_info
     * @param $currentDate
     * @param $language
     * @return array
     */
    private function _fillEventInfo($majorEventGenre,$event_info,$currentDate, $language)
    {
        $teams = $event_info->teams;
        $event_time = date('Y-m-d H:i', $event_info->event_time);
        list($date, $time) = explode(' ', $event_time);
        $event_time = $date == $currentDate ? $time : '24:00';

        //语言选项
        $title = self::_geti18n('title', $language);
        $team_name = self::_geti18n('team_name', $language);


        if (isset($teams[0]) && isset($teams[1])) {
            //比赛信息
            $event_info = [
                'title' => $event_info->$title,
                'event_time' => $event_time,
                'event_information' => [
                    'A' => [
                        'name' => $teams[0]->$team_name,
                        'icon' => Func::getAccessUrl($this->uid, $teams[0]->team_icon, 86400, 'first day of next month'),
                    ],
                    'B' => [
                        'name' => $teams[1]->$team_name,
                        'icon' => Func::getAccessUrl($this->uid, $teams[1]->team_icon, 86400, 'first day of next month'),
                    ]
                ],
                'event_info' => $teams[0]->$team_name . '-' . $teams[1]->$team_name,
                'event_info_icon' => [
                    Func::getAccessUrl($this->uid, $teams[0]->team_icon, 86400,'first day of next month'),
                    Func::getAccessUrl($this->uid, $teams[1]->team_icon, 86400, 'first day of next month'),
                ]
            ];
        } else {
            //比赛信息
            $event_info = [
                'title' => $majorEventGenre['title'],
                'event_time' => $event_time,
                'event_info' => $event_info->$title,
                'event_info_icon' => [

                ]
            ];
        }

        return $event_info;
    }

    /**
     * 填充国家信息
     * @param $channel_group
     * @return array
     */
    private function _fillCountry($channel_group)
    {
        $channel_list = [];
        foreach ($channel_group as $language => $channel_group_list) {
            //查找国家
            $_country = Capsule::table('sys_country')
                                    ->where('code' ,'=', $language)
                                    ->first();

            $_country_icon = isset($_country->icon) ? $_country->icon : null;

            $item = [
                'language' => $language,
                'language_icon' => Func::getAccessUrl($this->uid, $_country_icon , 86400),
            ];

            array_walk($channel_group_list, function($channel) use(&$item) {
                $item['channels'][] = [
                    'main_class' => $channel->main_class,
                    'channel_name' => $channel->channel_name,
                    'channel_id' => $channel->channel_id,
                    'channel_icon' => Func::getAccessUrl($this->uid, $channel->channel_icon)
                ];
            });
            $channel_list[] = $item;
        }

        return $channel_list;
    }

    /**
     * 获取主要赛事列表
     * @param $language string 语言 目前支持zh_cn en_us
     * @param $timezone integer string
     * @param $start_time integer
     * @param $end_time integer
     * @return array
     */
    public function majorEventForHour($language, $timezone, $start_time, $end_time)
    {
        //查数据库
        $data = $this->getMajorEventByTime($start_time, $end_time);
        $responseData = [];
        $eventData= [];

        if (!empty($data)) {
            foreach ($data as $val) {
                $eventData[$val['time']][] = $val;
            }
            foreach ($eventData as $start_time => $data) {
                $responseData[] = $this->_organizeData($data, $timezone, $start_time, $language);
            }
        }

        return $responseData;
    }

    private function getMajorEventByTime($start_time, $end_time)
    {
        //查数据库
        $data = Capsule::table('ott_major_event')
                        ->where([
                            ['base_time' ,'>=',  $start_time],
                            ['base_time', '<', $end_time]
                        ])
                        ->orderBy('base_time', 'ASC')
                        ->get();

        return ArrayHelper::toArray($data);
    }

    /**
     * 取一级分类列表
     * @return array
     */
    public function getMainClass(): array
    {
        $cacheKey = 'OTT_CLASSIFICATION';
        $cacheValue = $this->getDataFromCache($cacheKey, Redis::$REDIS_PROTOCOL);

        // 读取缓存
        if ($cacheValue) {

            if (isset($data['ver']) && $this->data['ver'] == $cacheValue['version']) {
                $this->stdout('没有数据', 'ERROR');
                return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_NEED_UPDATE];
            }

            return ['status' => true, 'data' => $cacheValue];
        }

        // 读取数据库
        $mainClass = Capsule::table('ott_main_class')
                            ->where('use_flag', '=', 1)
                            ->orderBy('sort', 'asc')
                            ->get();

        $mainClass = ArrayHelper::toArray($mainClass);

        if (!empty($mainClass)) {
            array_walk($mainClass, function(&$v){
                $v['code'] = $v['name'];
                if (strpos($v['icon'], '/') !== false) {
                    $v['commonpic'] = Func::getAccessUrl($this->uid, $v['icon'],864000);
                }
                if (strpos($v['icon_hover'], '/') !== false) {
                    $v['hoverpic'] = Func::getAccessUrl($this->uid, $v['icon_hover'] ,864000);
                }
                unset($v['icon'], $v['icon_hover']);
                $v['is_buy'] = true;
            });
        }

        $response['version'] = date('YmdHis');
        $response['data'] = $mainClass;

        $redis = $this->getRedis();
        $redis->set($cacheKey, json_encode($response));
        $redis->expire($cacheKey, 3600);

        return ['status' => true, 'data' => $response];
    }

    /**
     * 获取节目列表
     * @param $genre
     * @param $version
     * @param $scheme
     * @param $format
     * @param $access_key
     * @return array
     */
    public function getList($genre, $version, $scheme, $format, $access_key): array
    {
        $access = $this->judgeAccess($genre,$access_key);

        if ($access['status'] === false) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_PERMISSION_DENY];
        }

        $cache = Redis::singleton();
        $cache->getRedis()->select(Redis::$REDIS_PROTOCOL);

        //判断版本是否需要下发列表
        $cacheVersion = $cache->get(self::getVersion($genre, $scheme, $format));
        if ($cacheVersion == false) {
            $cacheVersion = $cache->get(self::getVersion($genre, 'ALL', $format));
        }

        if ($cacheVersion && $version >= $cacheVersion) {
            $this->stdout("无需更新", 'INFO');
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_NEED_UPDATE];
        }

        if ($list = $cache->get(self::getKey($genre, $scheme, true))) {
            return ['status' => true, 'data' => $list];
        }

        if ($format == 'XML') {
            $data = $this->EncryptXML(self::getKey($genre, $scheme, $format));
            if (empty($data)) {
                $data = $this->EncryptXML(self::getKey($genre, 'ALL', $format));
            }
        } else {
            $data = $this->encryptJson(self::getKey($genre, $scheme, $format));
            if (empty($data)) {
                $data = $this->encryptJson(self::getKey($genre, 'ALL', $format));
            }
        }

        if (empty($data)) {
            $this->stdout("没有数据", 'ERROR');
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        return ['status' => true, 'data' => $data];
    }

    /**
     * 根据收费模式判断用户的权限
     * @param $genreName
     * @param $access_key
     * @return array
     */
    private function judgeAccess($genreName, $access_key)
    {
        if (CHARGE_MODE == 1) {
             return $this->chargeWithMember();
        } else if(CHARGE_MODE == 2) {
             return $this->chargeWithGenre($genreName,$access_key);
        }

        return ['status' => true, 'msg' => 'ok'];
    }

    /**
     * 根据分类收费
     * @param $genreName
     * @param $access_key
     * @return array
     */
    protected function chargeWithGenre($genreName, $access_key)
    {
        // 判断是否为收费类别
        $genre = Capsule::table('ott_main_class')
                        ->select('is_charge','free_trail_days','list_name')
                        ->where('list_name', '=', $genreName)
                        ->first();

        if (!is_null($genre) && $genre->is_charge) {
            return $this->genreCharged($genre, $access_key);
        } else {
            return $this->genreFree();
        }
    }

    /**
     * 免费的时候
     * @return array
     */
    protected function genreFree()
    {
        return ['status' => true, 'msg' => 'ok'];
    }

    /**
     * 收费的时候
     * @param $genre
     * @param $access_key
     * @return array
     */
    protected function genreCharged($genre, $access_key)
    {
        if ($access_key) {
            // 判断查询Access_key 判断权限表
            $genreAccess = Capsule::table('ott_access')
                ->select(['is_valid','expire_time', 'deny_msg', 'access_key'])
                ->where([
                    ['mac', '=',  $this->uid],
                    ['genre', '=',  $genre->list_name],
                    ['access_key', '=', $access_key]
                ])
                ->first();

            if (is_null($genreAccess)) {
                return $this->judgeWhenAccessKeyNotExist($genre);
            } else {
                return $this->judgeWhenAccessKeyExist($genreAccess, $genre->list_name, $access_key);
            }
        }

        return $this->judgeWhenAccessKeyNotExist($genre);
    }

    /**
     * access_key 不存在时判断试用期
     * @param $genre
     * @return array
     */
    protected function judgeWhenAccessKeyNotExist($genre)
    {
        // 查询是否已经存在试用期
        $probation = Capsule::table('ott_genre_probation')
                            ->where([
                                ['mac', '=',  $this->uid],
                                ['genre', '=', $genre->list_name],
                            ])
                            ->first();

        if (!empty($probation)) {
            if ($probation->expire_time > time()) {
                return ['status' => true, 'msg' => 'ok'];
            } else {
                $this->stdout("分类过期", 'ERROR');
                return ['status' => false, 'msg' => "试用过期"];
            }
        }

        // 查询免费使用天数
        $day = $genre->free_trail_days;
        if ($day <= 0) {
            return ['status' => false, 'msg' => "分类收费，并且没有试用期"];
        }

        $expireTime = strtotime("+ {$day}day");

        Capsule::beginTransaction();
        try {

            Capsule::table('ott_genre_probation')
                ->insert([
                    'mac'  => $this->uid,
                    'genre' => $genre->list_name,
                    'day' => date('Y-m-d'),
                    'expire_time' => $expireTime,
                    'created_at' => time(),
                    'updated_at' => time()
                ]);

            Capsule::commit();

            return ['status' => true, 'msg' => 'ok'];

        } catch (\Exception $e) {
            Capsule::rollback();
            $this->stdout("数据库事务回滚" . $e->getMessage(), 'ERROR');

            return ['status' => false, 'msg' => '服务器错误'];
        }
    }

    /**
     * 当access_key 存在的时候
     * @param $genreAccess
     * @param $class
     * @param $access_key
     * @return array
     */
    protected function judgeWhenAccessKeyExist($genreAccess, $class, $access_key)
    {
        if ($access_key && $access_key != $genreAccess->access_key) {
            return ['status' => false, 'msg' => 'expire'];
        }

        // 不为空
        if ($genreAccess->is_valid == 1) {
            // 判断是否过期 如果过期 更新状态
            if ($genreAccess->expire_time < time()) {
                Capsule::table('ott_access')
                    ->where([
                        ['mac', '=',  $this->uid],
                        ['genre', '=', $class],
                        ['access_key', '=', $access_key]
                    ])
                    ->update([
                        'is_valid' => 0,
                        'deny_msg' => 'expire'
                    ]);
                $this->stdout("分类过期", 'ERROR');

                return ['status' => false, 'msg' => 'expire'];
            }

            return ['status' => true, 'msg' => $genreAccess->deny_msg];
        } else {
            $this->stdout("分类过期", 'ERROR');

            return ['status' => false, 'msg' => $genreAccess->deny_msg];
        }
    }

    /**
     * 根据会员收费
     * @return array
     */
    protected function chargeWithMember()
    {
        // 判断用户是否有权限(判断是否为会员)
        $user = Capsule::table('yii2_user')
                        ->select('is_vip')
                        ->where('username', '=', $this->uid)
                        ->first();

        if ($user->is_vip == false) {
            $this->stdout("不是会员", 'ERROR');
            return ['status' => false, 'msg' => '不是会员'];
        }

        return ['status' => true, 'msg' => 'ok'];
    }

    /**
     * 获取正则列表
     * @param $version
     * @return array
     */
    public function getRegex($version): array
    {
        //查缓存
        $cacheKey = "resolve_list";
        $redisDB = Redis::$REDIS_PROTOCOL;

        $cacheValue = $this->getDataFromCache($cacheKey, $redisDB);

        if ($cacheValue) {
            if ($cacheValue['version'] == $version) {
                $this->stdout("无需更新", 'INFO');
               return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_NEED_UPDATE];
            }

            return ['status' => true, 'data' => $cacheValue];
        }

        //查数据库
        $data = Capsule::table('iptv_url_resolution')
                         ->select(['c','android','expire_time', 'referer','method','url'])
                         ->get();

        $data = ArrayHelper::toArray($data);

        if (empty($data)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        foreach ($data as $key => $value) {
            $value['c']       = json_decode($data[$key]['c'], true);
            $value['android'] = json_decode($data[$key]['android'], true);
        }

        $response['version'] = date('YmdHis');
        $response['data'] = $data;
        $this->getRedis($redisDB)->set($cacheKey, json_encode($response));

        return ['status' => true, 'data' => $response];
    }

    // 获取全部的预告数据
    public function getEPG($genre, $version): array
    {
        $redisKey = "ALL_" . strtoupper($genre) . "_PARADE_LIST";
        $redisDB = Redis::$REDIS_EPG;
        $cache = $this->getDataFromCache($redisKey, $redisDB);

        if ($cache) {
            if ($cache['version'] == $version) {
                $this->stdout("无需更新", 'INFO');
                return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_NEED_UPDATE];
            }

            return ['status' => true, 'data' => $cache];
        }

        // 查询该分类
        $items = Capsule::table('iptv_middle_parade')
                          ->where('genre', '=', $genre)
                          ->get();

        if (is_null($items)) {
            $this->stdout("没有数据", 'ERROR');
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        $items = ArrayHelper::toArray($items);
        $parade = [];

        foreach ($items as $item) {
            $paradeContents = json_decode($item['parade_content'], true);
            if ($paradeContents) {
                $channelParades = [];
                foreach ($paradeContents as $paradeContent) {
                    $paradeData = json_decode($paradeContent['parade_data'], true);
                    $channelParades[] = [
                        'parade_date' => $paradeContent['parade_date'],
                        'parade_data' => $paradeData
                    ];
                }
            }

            if (!empty($channelParades)) {

                array_multisort(array_column($channelParades,'parade_date'),SORT_ASC, $channelParades);
                $parade[] = [
                    'name' => $item['channel'],
                    'items' => $channelParades
                ];
            }
        }

        $response['version'] = date('YmdHi');
        $response['items'] = $parade ;

        $redis = $this->getRedis($redisDB);
        $redis->set($redisKey, json_encode($response));
        $redis->expire($redisKey, 3600);

        if (empty($parade)) {
            $this->stdout("没有数据", 'ERROR');
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        return ['status' => true, 'data' => $response] ;
    }

    /**
     * 获取某个分类下所有频道的预告 有则出现
     * @return array
     * @throws \Exception
     */
    public function getGenreParadeList()
    {
        try {
            $genre = $this->post('genre');
            $day = $this->post('day', 1, ['integer', 'min'=>1, 'max'=>7]);
            $timezone = $this->post('timezone', '+8');
        } catch (\InvalidArgumentException $e) {
            $this->stdout("参数错误", 'ERROR');
            return ['status' => false, 'code' => $e->getCode()];
        }

        // 查询该分类
        $class = Capsule::table('ott_sub_class')
                          ->where([['name', '=',  $genre], ['use_flag', '=',  1]])
                          ->select('id')
                          ->first();

        if (empty($class)) {
            $this->stdout("没有数据", 'ERROR');
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        $channels = Capsule::table('ott_channel')
                             ->where([['sub_class_id', '=', $class->id],['use_flag', '=', '1']])
                             ->select('name,alias_name')
                             ->get();

        $channels = ArrayHelper::toArray($channels);

        if (empty($channels)) {
            $this->stdout("没有数据", 'ERROR');
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        $responseData = [];
        foreach ($channels as $channel) {
             if (!empty($channel['alias_name'])) {
                 $parade = $this->_getParadeTvBox($channel['alias_name'], $day, $timezone);
                 if ($parade != false) {
                     $responseData[$channel['name']] = $parade['parade'];
                 }
             }
        }

        if (empty($responseData)) {
            $this->stdout("没有数据", 'ERROR');
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        return $responseData;
    }

    /**
     * 兼容旧版 预告
     * @return mixed
     */
    public function getParadeListTvBox()
    {
        try {
            $name = $this->post('name',null, ['required']);
            $day = $this->post('day', 1, ['integer', 'min'=>1, 'max'=>7]);
            $timezone = $this->post('timezone', '+8');
        } catch (\Exception $e) {
            $this->stdout("参数错误", 'ERROR');
            return ['status' => false, 'code' => $e->getCode() ];
        }

        $response = $this->_getParadeTvBox($name, $day, $timezone);
        return $response;
    }

    private function _getParadeTvBox($name, $day, $timezone)
    {
        $data = $this->_getParadeByName($name, $day, $timezone);

        $key = ['Yesterday', 'Today', 'Tomorrow', 'Tomorrow-1', 'Tomorrow-2', 'Tomorrow-3', 'Tomorrow-4', 'Tomorrow-5'];

        $response['channel'] = $name;
        $response['day'] = $day;
        $response['parade'] = [];

        $index = 0;
        if ($data) {
            foreach ($data as $date => $val) {
               if (!empty($val)) {
                   foreach($val as $_val) {
                       $response['parade'][$key[$index]][] = [
                           'time' => date('Y-m-d', $_val['parade_timestamp']) . ' ' . $_val['parade_time'],
                           'name' => $_val['parade_name']
                       ];
                   }
                   $index++;
               }
            }
        }


        return $response;
    }

    /**
     * 获取预告列表
     * @param $name
     * @param $day
     * @param $timezone
     * @return array
     */
    public function getParadeList($name, $day, $timezone): array
    {
        $responseData = $this->_getParadeByName($name, $day, $timezone);

        if ($responseData == false) {
            $this->stdout("没有数据", 'ERROR');
            return ['code' => ErrorCode::$RES_ERROR_NO_LIST_DATA, 'status' => false];
        }

        return ['status' => false, 'data' => $responseData];
    }

    /**
     * 通过name 查询预告
     * @param $name
     * @param $day
     * @param $timezone
     * @return array|bool
     */
    private function _getParadeByName($name, $day, $timezone)
    {
        $data = Capsule::table('iptv_parade')
                            ->where('channel_name', '=', $name)
                            ->orderBy('parade_date', 'ASC')
                            ->get();

        $data = ArrayHelper::toArray($data);

        return $this->_getParade($data, $day, $timezone);
    }



    /**
     * 根据时间 时区从数据库中获取预告列表
     * @param $data
     * @param $day
     * @param $timezone
     * @return array|bool
     */
    private function _getParade($data, $day, $timezone)
    {
        if (empty($data)) return false;

        $responseData = [];
        //重新计算时间

        foreach ($data as $val) {
            $parade = json_decode($val['parade_data'], true);
            array_walk($parade, function(&$v) use($timezone, &$responseData) {
                $date = $this->getLocalTime($timezone, 'Y-m-d', $v['parade_timestamp']);
                $v['parade_time'] = $this->getLocalTime($timezone, 'H:i', $v['parade_timestamp']);
                $responseData[$date][] = $v;
            });
        }

        //计算开始时间
        $during = $this->getDuring($timezone, 7);
        $dates = array_slice(array_column($during, 'date'), 0, $day);


        foreach ($responseData as $date => $data) {
            if (!in_array($date, $dates)) {
                unset($responseData[$date]);
            } else {
                $responseData[$date] = ArrayHelper::sort($data, 'parade_timestamp',SORT_ASC);
            }

        }

        if (empty($responseData)) return false;

        return $responseData;
    }

    /**
     * 国际化字段
     * @param $field
     * @param $lang
     * @return mixed
     */
    static private function _geti18n($field, $lang)
    {
        if ($lang == 'en') {
            return $field;
        }

        $fieldMap = [
            'zh_cn' => [
                'title' => 'title_zh',
                'event_info' => 'event_info',
                'team_name' => 'team_zh_name'
            ]
        ];

        if (!isset($fieldMap[$lang]) || !isset($fieldMap[$lang][$field])) {
            return $field;
        }

        return $fieldMap[$lang][$field];
    }

    /**
     * 按客户端本地时区计算开始起始时间戳-结束时间戳
     * @param $timezone integer 时区
     * @param $day integer
     * @return array
     */
    private function getDuring($timezone, $day)
    {
        //计算出客户端时区0点时间戳
        date_default_timezone_set("UTC");
        $offset = $timezone * 3600;
        $start = strtotime(date('Y-m-d')) - $offset;

        $during = [];
        for ($i=1; $i<=$day; $i++) {
            $during[] = [
                'date' => date('Y-m-d', $start),
                'start' => $start,
                'end' => $start + 86400
            ];
            $start += 86400;
        }

        return $this->convertToLocal($during, $timezone);
    }

    /**
     * 按客户端本地时区计算开始起始时间戳-结束时间戳(小时)
     * @param $timezone
     * @param $hour
     * @return array
     */
    private function getDuringHour($timezone, $hour)
    {
        //计算出客户端时区0点时间戳
        date_default_timezone_set("UTC");
        $start = time();
        $end = $start + $hour * 3600;

        $this->setTimeZone($timezone);
        return [
            'start' => $start,
            'end' => $end
        ];
    }

    /**
     * 转成客户端时区时间
     * @param $data
     * @param $timezone
     * @return mixed
     */
    private function convertToLocal($data,$timezone)
    {
        $this->setTimeZone($timezone);
        foreach ($data as &$val) {
            $val['date'] = date('Y-m-d', $val['start']);
        }

        return $data;
    }

    /**
     * 获取key
     * @param $class
     * @param $scheme
     * @param $format
     * @return string
     */
    private static function getVersion($class, $scheme, $format)
    {
        //      OTT_LIST_XML_MITV_HDBOX_MiTV_VERSION
        return "OTT_LIST_{$format}_{$class}_{$scheme}_VERSION";
    }

    /**
     * 获取key
     * @param $class
     * @param $scheme
     * @param $format
     * @param bool $encrypt
     * @return string
     */
    private static function getKey($class, $scheme, $format, $encrypt = false)
    {
        return ($encrypt ? 'ENCRYPT_' : "") . "OTT_LIST_{$format}_{$class}_{$scheme}";
    }

    /**
     * 加密节目列表 JSON格式
     * @param $cacheKey
     * @return bool|mixed|string
     */
    private function encryptJson($cacheKey)
    {
        $cache = Redis::singleton();
        $cache->getRedis()->select(Redis::$REDIS_PROTOCOL);

        $list = $cache->get($cacheKey);

        if (!isset($list) || $list == false) {
            return false;
        }

        //加密处理
        AES::setKEY(AES::$_KEY);
        $list = json_decode($list, true);

        $list['icon'] = Func::getAccessUrl($this->uid, $list['icon'], 86400);

        foreach ($list['subClass'] as &$class) {
            foreach ($class['channels'] as &$channel) {
                $channel['image'] = Func::getAccessUrl($this->uid, $channel['image'], 86400);
                foreach ($channel['links'] as &$link) {
                    $link['link'] = AES::encrypt($link['link']);
                }
            }
        }

        $list = json_encode($list);
        $cache->getRedis()->set("ENCRYPT_" . $cacheKey , $list);
        $cache->getRedis()->set("ENCRYPT_" . $cacheKey . "_updatetime",time());

        Formatter::format(Formatter::JSON);
        return $list;
    }

    /**
     * 加密列表 XML
     * @param $cacheKey
     * @return bool|mixed
     */
    private function EncryptXML($cacheKey)
    {

        $cache = Redis::singleton();
        $cache->getRedis()->select(Redis::$REDIS_PROTOCOL);

        $list = $cache->get($cacheKey);

        if (!isset($list) || $list == false) {
            return false;
        }

        //加密处理
        AES::setKEY(AES::$_KEY);

        $list = preg_replace_callback('/CDATA\[\S+\]]/',function($match) {
            return "CDATA[".AES::encrypt(substr(substr($match[0],6),0,-2))."]]";
        },$list);

        $lists = preg_replace_callback('/url="\s*\S+\s*"/',function($match) {
            $afterSub = trim(substr($match[0],4),'"');
            return 'url="'.AES::encrypt($afterSub).'"';
        },$list);

        $cache->getRedis()->set("ENCRYPT_" . $cacheKey , $lists);
        $cache->getRedis()->set("ENCRYPT_" . $cacheKey . "_updatetime",time());

        return $lists;

    }


    /**
     * 获取频道图标
     * @param $name
     * @return array
     */
    public function getChannelIcon($name): array
    {
        $channel = Capsule::table('ott_channel')
                            ->where('name' ,'=', $name)
                            ->first();

        if (empty($channel) || empty($channel->image)) {
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        return [
            'data' => [
                'name' => $name,
                'channelPic' => Func::getAccessUrl($this->uid, $channel->image)
            ],
            'status' => true
        ];
    }

    /**
     * @param $name
     * @return array
     */
    public function getGenre($name):array
    {
        // 读取数据库
        $mainClass = Capsule::table('ott_main_class')
                    ->where([['use_flag', '=', 1],['name' ,'=', $name]])
                    ->orderBy('sort', 'asc')
                    ->first();

        if (is_null($mainClass)) {
            $this->stdout("没有数据", 'ERROR');
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        $mainClass = ArrayHelper::toArray($mainClass);

        if (!empty($mainClass)) {
            $mainClass['code'] = $mainClass['name'];
            if (strpos($mainClass['icon'], '/') !== false) {
                $mainClass['commonpic'] = Func::getAccessUrl($this->uid, $mainClass['icon'],864000);
            }
            if (strpos($mainClass['icon_hover'], '/') !== false) {
                $mainClass['hoverpic'] = Func::getAccessUrl($this->uid, $mainClass['icon_hover'] ,864000);
            }
            unset($mainClass['icon'], $mainClass['icon_hover']);
            $mainClass['is_buy'] = true;
        }

        // 查询当前用户是否有权限观看
        $result = Capsule::table('ott_order')
                            ->where([['uid', '=', $this->uid], ['is_valid', '=', 1]])
                            ->exists();

        if (!is_null($result)) {
            $mainClass['is_buy'] = 0;
        } else {
            $mainClass['is_buy'] = 1;
        }

        return ['status' => true, 'data' => $mainClass];
    }

    /**
     * 获取分类状态
     * @param $genre
     * @param $access_key
     * @return array
     */
    public function getGenreUsageInfo($genre, $access_key): array
    {
        $mainClass = Capsule::table('ott_main_class')
                            ->where([
                                ['list_name', '=', $genre]
                            ])
                            ->select(['name', 'list_name', 'is_charge', 'price'])
                            ->orderBy('sort', 'asc')
                            ->first();

        if (is_null($mainClass)) {
            $this->stdout("没有数据", 'ERROR');
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        $mainClass = ArrayHelper::toArray($mainClass);

        if (empty($access_key)) {
            $probation = Capsule::table('ott_genre_probation')
                            ->where([
                                ['mac', '=', $this->uid],
                                ['genre', '=', $genre]
                            ])
                            ->first();

            if (is_null($probation)) {
                $mainClass['state'] = 'access deny';
                $mainClass['expire_time'] = '';
            } else {
                $mainClass['state'] = 'probation expired';
                $mainClass['expire_time'] = $probation->expire_time;
            }

            return ['status' => true, 'data' => $mainClass];
        }

        // 查询
        $usage = Capsule::table('ott_access')
                              ->where([
                                  [ 'mac', '=', $this->uid],
                                  ['genre', '=', $genre],
                                  ['access_key', '=', $access_key]
                              ])
                              ->first();

        if (is_null($usage)) {
            $mainClass['state'] = 'access deny';
            $mainClass['expire_time'] = '';
        } else {
            $mainClass['expire_time'] = $usage->expire_time;
            $mainClass['state'] = $usage->deny_msg;
        }

        return ['status' => true, 'data' => $mainClass];
    }

    public function getGenrePrice($genre, $lang): array
    {
        $languageOptions = [
            'en_US' => ['1 month', '3 month', '6 month', '1 year'],
            'zh_CN' => ['1个月', '3个月', '6个月', '1年'],
        ];

        $lang = isset($languageOptions[$lang]) ? $languageOptions[$lang] : $languageOptions['en_US'];

        $mainClass = Capsule::table('ott_main_class')
                            ->where([
                                ['use_flag', '=', 1],
                                ['list_name', '=', $genre]
                            ])
                            ->select(['name', 'list_name', 'is_charge', 'one_month_price', 'three_month_price', 'six_month_price', 'one_year_price'])
                            ->first();

        if (empty($mainClass)) {
            $this->stdout("没有数据", 'ERROR');
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        $data = [
            'currency' => 'USD',
            'items' => [
                [
                    'text' => $lang[0],
                    'type' => 1,
                    'price' => $mainClass->one_month_price,
                ],
                [
                    'text' => $lang[1],
                    'type' => '3',
                    'price' => $mainClass->three_month_price,
                ],
                [
                    'text' => $lang[2],
                    'type' => '6',
                    'price' => $mainClass->six_month_price,
                ],
                [
                    'text' => $lang[3],
                    'price' => $mainClass->one_year_price,
                    'type' => '12',
                ]
            ]
        ];

        return ['status' => true, 'data' => $data];
    }

    /**
     * 给分类进行激活操作
     * @param $genre
     * @param $cardSecret
     * @param $sign
     * @param $timestamp
     * @return array
     */
    public function activateGenre($genre, $cardSecret, $sign, $timestamp): array
    {
        $serverSign = md5(md5($timestamp . 'topthinker' . $cardSecret));
        if ($serverSign != $sign) {
            $this->stdout("签名错误", 'ERROR');
            return ['status' => false, 'code' =>ErrorCode::$RES_ERROR_INVALID_SIGN];
        }

        $exist = Capsule::table('mac')->where('MAC', '=', $this->uid)->exists();
        if ($exist == false) {
            $this->stdout("用户不存在", 'ERROR');
            return ['status' => false, 'code' =>ErrorCode::$RES_ERROR_UID_NOT_EXIST];
        }

        $card = Capsule::table('sys_renewal_card')
                        ->select(['card_num','is_valid','is_del','card_contracttime'])
                        ->where('card_secret',"=", $cardSecret)
                        ->first();

        if (is_null($card) || !$card->is_valid || $card->is_del) {
            $this->stdout("卡无效", 'ERROR');
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_INVALID_CARD];
        }

        // 计算延续时间
        $addTime = strtotime("+ {$card->card_contracttime}") - time();

        //查询用户状态
        $access = Capsule::table('ott_access')
                        ->where([
                            ['genre', '=', $genre],
                            ['mac', '=', $this->uid]
                        ])
                        ->first();

        // 开启事务
        Capsule::beginTransaction();

        try {

            $access_key = md5($cardSecret . $this->uid);

            $expire_time = time() +  $addTime;
            Capsule::table('ott_access')
                ->insert([
                    'mac' => $this->uid,
                    'genre' => $genre,
                    'is_valid' => 1,
                    'expire_time' =>  $expire_time,
                    'deny_msg' => 'normal usage',
                    'access_key' => $access_key
                ]);

            Capsule::table('sys_renewal_card')
                ->where('card_num', '=', $card->card_num)
                ->update(['is_valid' => 0]);

            Capsule::table('iptv_renew')->insert(
                [
                    'mac' => $this->uid,
                    'card_num' => $card->card_num,
                    'date' => time(),
                    'renew_period' => $card->card_contracttime,
                    'expire_time' => date('Y-m-d H:i:s', $expire_time)
                ]
            );

            Capsule::commit();

            return [
                'status' => true,
                'data' => [
                    'mac' => $this->uid,
                    'genre' => $genre,
                    'expire_time' => $expire_time,
                    'access_key' => $access_key
                ]
            ];

        } catch (\Exception $e) {
            Capsule::rollback();
        }

        $this->stdout("服务器内部错误", 'ERROR');
        return ['status' => false, 'code' => ErrorCode::$RES_ERROR_SERVICE_IS_TEMPORARILY_UNAVAILABLE];

    }

    // 隐藏内容锁状态
    public function getLockStatus(): array
    {
        $mac = Capsule::table('mac')
                        ->select('is_hide')
                        ->where('MAC' ,'=', $this->uid)
                        ->first();

        if (!is_null($mac) && $mac->is_hide == 1) {
            return ['status' => true, 'data' => ['display' => 'hide']];
        } else {
            return ['status' => true, 'data' => ['display' => 'show']];
        }
    }

    // 解锁内容
    public function relieveLock(): array
    {
        $mac = Capsule::table('mac')
                        ->where('mac' ,'=', $this->uid)
                        ->first();

        if (!is_null($mac)) {
            Capsule::table('mac')
                        ->where('mac' ,'=', $this->uid)
                        ->update([
                            'is_hide' => 0
                        ]);
        }

        return ['status' => true, 'data' => [
            'display' => 'show'
        ]];

    }

}