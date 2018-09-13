<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/4/13
 * Time: 16:09
 */
namespace App\Service;

use App\Components\helper\ArrayHelper;
use App\Components\Aliyun\MyOSS;
use App\Components\helper\Func;
use App\Exceptions\ErrorCode;
use Illuminate\Database\Capsule\Manager as Capsule;

class appService extends common
{
    /**
     * 获取App市场列表
     * @return array
     */
    public function getAppMarket()
    {
        //验证token
        try {
            $sign = $this->post('sign');
            $time = $this->post('time');
            $scheme = $this->post('scheme', 'all');
            $page = $this->post('page', 1);
            $limit = $this->post('per_page', 10, ['integer' ,'min'=>5,'max'=>100]);
        } catch (\Exception $e) {
            $this->stdout($e->getMessage(), 'ERROR');
            return ['status' => false, 'code' => $e->getCode()];
        }

        $serverSign = md5(md5('topthinker'.$time.$this->uid));

        if ($sign != $serverSign) {
            // return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA]
        }

        $offset = $limit * ($page-1);
        $where = [];

        if ($scheme) {
            $scheme = Capsule::table('sys_scheme')
                               ->where('schemeName', '=', $scheme)
                               ->first();

            if (isset($scheme->id) && !empty($scheme->id)) {
                $schemeID = $scheme->id;
                $schemeID = Capsule::table('sys__apk_scheme')
                                   ->where('scheme_id', '=', $schemeID)
                                   ->get();

                $schemeID = ArrayHelper::toArray($schemeID);

                if (!empty($schemeID)) {
                    $schemeID = array_filter(array_column($schemeID, 'apk_id'));
                }
            }
        }

        $query = $data = Capsule::table('apk_list AS a')
                                ->leftJoin('apk_detail AS b', 'a.ID', '=', 'b.apk_ID')
                                ->where($where)
                                ->orderBy('a.sort','ASC')
                                ->limit($limit)
                                ->offset($offset)
                                ->where('is_newest', '=', 1);

        if (isset($schemeID)) {
            $query->whereIn('a.ID', $schemeID);
        }

        $data = $query->get();
        $total = $data->count();
        if ($total == false) {
            $this->stdout("没有数据", 'ERROR');
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        $data = ArrayHelper::toArray($data);
        foreach ($data as $k => $app) {
            if (!empty($app['img']) && !preg_match('/^http/',$app['img'])) {
                $data[$k]['img'] = Func::getAccessUrl($this->uid, $app['img'], 86400);
            }
            if (!empty($app['url'])) {
                $data[$k]['url'] = Func::getAccessUrl($this->uid, $app['url'], 86400);
            }
        }

        $market['scheme'] = ":" . $scheme->schemeName;
        $market['total'] = $total;
        $market['totalPage'] = ceil($total/$limit);
        $market['page'] = $page;
        $market['apps'] = $data;

        return ['status' => true, 'data' => $market];
    }

    /**
     * APP市场获取某个App
     * @return array
     */
    public function getConcreteApp()
    {
        try {
            $sign = $this->post('sign');
            $time = $this->post('time');
            $appId = $this->post('appid');
        } catch (\Exception $e) {
            return ['status' => true, 'code' => $e->getCode()];
        }

        $ServerSign = md5(md5('topthinker'. $time . $this->uid));

        if (empty($sign) || empty($time) || $sign != $ServerSign) {
            $this->stdout("签名错误", 'ERROR');
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_SIGNATURE];
        }

        $app = Capsule::table('apk_detail')
                            ->where('ID','=', $appId)
                            ->first();

        if (is_null($app) || !isset($app->url)) {
            $this->stdout("没有数据", 'ERROR');
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        $object = $app->url;
        $ver = $app->ver;
        $url = Func::getAccessUrl($this->uid, $object, 86400);
        
        return [
            'status' => true,
            'data' => [
                'ver' => $ver,
                'url' => $url
            ]
        ];

    }

    /**
     * App 更新
     * @return array
     */
    public function updateApp()
    {
        try {
            $ver = $this->post('ver', 0);
            $type = $this->post('type');
        } catch (\Exception $e) {
            $this->stdout($e->getMessage(), 'ERROR');
            return ['status' => false, 'code' => $e->getCode()];
        }

        $ver = str_replace('_', '.', $ver);

        $lastApp = $this->getLastApp($type);
        $max_ver = $lastApp['ver'];

        if ($lastApp == false) {
            $this->stdout("没有数据", 'ERROR');
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        if ($this->judgeIsNeedUpdate($ver,$max_ver) == false) {
            $this->stdout("无需更新", 'ERROR');
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_NEED_UPDATE];
        }

        if (isset($lastApp['type']) && $lastApp['type']==1) {
            $oss = new MyOSS();
            if (strpos($lastApp['url'], '/') === false && $oss->is_exist($lastApp['url'])) {
                $lastApp['url'] = $oss->getSignUrl($lastApp['url'], 1000);
            } else {
                $lastApp['url'] = Func::getAccessUrl($this->uid, $lastApp['url'], 1000);
            }
        }

        $lastApp['type'] = $lastApp['typeName'];

        unset($lastApp['typeName']);
        unset($lastApp['ID']);
        unset($lastApp['apk_ID']);

        return ['status' => true, 'data' => $lastApp];
    }

    public function judgeIsNeedUpdate($client,$server)
    {
        if (is_null($client)) {
            return true;
        }
        $clientVersion = ltrim(strtolower($client),'v');
        $serverVersion = ltrim(strtolower($server),'v');
        $res = strnatcmp($serverVersion,$clientVersion);
        if ($res == 1) {
            return true;
        }else{
            return false;
        }

    }

    private function getLastApp($type)
    {
        $apk_list = Capsule::table("apk_list")
                            ->select(['ID','class','type'])
                            ->where('type', '=' , $type)
                            ->first();

        if ($apk_list) {
            $apk_detail = Capsule::table('apk_detail')
                                ->select(['url', 'content', 'md5', 'ver', 'force_update','type'])
                                ->where('apk_ID', '=' , $apk_list->ID)
                                ->orderBy('ID', 'DESC')
                                ->first();

            $apk_detail = ArrayHelper::toArray($apk_detail);

            if ($apk_detail){
                $apk_detail['classes'] = $apk_list->class;
                $apk_detail['typeName'] = $apk_list->type;
                return $apk_detail;
            }
        }

        return false;
    }

    /**
     * 获取APP进入图片
     * @return mixed
     */
    public function getBootPic()
    {
        $data = Capsule::table('app_boot_picture')
                         ->where('status', '=', '1')
                         ->get();

        if ($data->count() == false) {
            $this->stdout("没有数据", 'ERROR');
            return ['status' => false, 'code' => ErrorCode::$RES_ERROR_NO_LIST_DATA];
        }

        $data = ArrayHelper::toArray($data);

        array_walk($data, function(&$v, $k){
            $v['boot_pic_url'] = Func::getAccessUrl($this->uid, $v['boot_pic'], 600);

            if ($v['during'] && strpos('-', $v['during'])) {
                $during = explode(' - ', $v['during']);
                $v['start'] = strtotime($during[0]);
                $v['end'] = strtotime($during[1]);
            } else {
                $v['start'] = time();
                $v['end'] = time();
            }
            unset($v['during'], $v['created_time']);
        });

        return ['status' => true, 'data' => $data];
    }

}