<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/19
 * Time: 9:51
 */

namespace App\Controller;

use App\Exceptions\ErrorCode;
use App\Models\ListSearcher;
use App\Models\VodSearcher;
use App\Service\authService;
use App\Components\http\Formatter;
use App\Service\iptvService;

class IptvController extends BaseController
{
    /**
     * 登录认证
     * @return array
     */
    public function auth()
    {
        try {
            $mac       = $this->uid;
            $timestamp = $this->post('timestamp', time());
            $signature = $this->post('signature','');
        } catch (\Exception $e) {
           return $this->fail($e->getCode(), 'yii');
        }

        $authService = new authService($this->request);
        $token = $authService->login($mac, $timestamp, $signature);
        if ($token['status'] === false) {
            $this->setError($token['code']);
            return Formatter::back([],$token['code']);
        }

        return Formatter::yiiBack($token['data']);
    }

    /**
     * 获取 banner 图
     * @return mixed
     */
    public function getBanner(): array
    {
        $iptvService = new iptvService($this->request);
        $data = $iptvService->getBanner();
        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    /**
     * 获取提供搜索的字段以及值
     * @return mixed
     */
    public function getType(): array
    {
        $expand = $this->request->get('expand');
        $iptvService = new iptvService($this->request);
        $data = $iptvService->getType($expand);
        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    public function getVods(): array
    {
        $searcher = new VodSearcher();
        $searcher->cid      = $this->request->get('cid')  ?? ($this->request->get('vod_cid') ?? false);
        $searcher->name     = $this->request->get('name') ?? ($this->request->get('vod_name') ?? false);
        $searcher->type     = $this->request->get('type') ?? ($this->request->get('vod_type') ?? false);
        $searcher->year     = $this->request->get('year') ?? ($this->request->get('vod_year') ?? false);
        $searcher->area     = $this->request->get('area') ?? ($this->request->get('vod_language') ?? false);
        $searcher->page     = $this->request->get('page') ?? 1;
        $searcher->per_page = $this->request->get('per_page') ?? 12;
        $searcher->genre    = $this->request->get('genre', 'Movie');
        $searcher->letter = strtoupper($this->request->get('letter', ''));
        $searcher->keyword = strtoupper($this->request->get('keyword', ''));


        $iptvService = new iptvService($this->request);
        $data = $iptvService->getVods($searcher);
        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    public function getVod($id): array
    {
        $expand = $this->request->get('expand', false);
        $iptvService = new iptvService($this->request);
        $data = $iptvService->getVod($id, $expand);
        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    public function getVodLinks($vod_id): array
    {
        $iptvService = new iptvService($this->request);
        $data = $iptvService->getVodLinks($vod_id);
        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    public function getRecommends($id): array
    {
        $num = $this->request->get('num', 4);
        $iptvService = new iptvService($this->request);
        $data = $iptvService->getRecommends($id, $num);
        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    public function vodHome(): array
    {
        $iptvService = new iptvService($this->request);
        $data = $iptvService->vodHome();
        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    public function getCondition(): array
    {
        $list_id = $this->request->get('vod_id');
        $iptvService = new iptvService($this->request);
        $data = $iptvService->getCondition($list_id);
        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    public function getLink($id): array
    {
        $iptvService = new iptvService($this->request);
        $data = $iptvService->getLink($id);
        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    public function getCategory(): array
    {
        $type = $this->request->get('type', 'Movie');
        $language = $this->request->get('lang', 'en_US');

        $iptvService = new iptvService($this->request);
        $data = $iptvService->getCategory($type, $language);

        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    public function getByHot(): array
    {
        $type = $this->request->get('type', 'Movie');
        $language = $this->request->get('lang', 'en-us');

        $iptvService = new iptvService($this->request);
        $data = $iptvService->getDimensionData($type, 'hot', $language);

        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    public function getByType(): array
    {
        $type = $this->request->get('type', 'Movie');
        $language = $this->request->get('lang', 'en-us');

        $iptvService = new iptvService($this->request);
        $data = $iptvService->getDimensionData($type, 'type', $language);

        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    public function getByYear(): array
    {
        $type = $this->request->get('type', 'Movie');
        $language = $this->request->get('lang', 'en-us');

        $iptvService = new iptvService($this->request);
        $data = $iptvService->getDimensionData($type, 'year', $language);

        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    public function getByArea(): array
    {
        $type     = $this->request->get('type', 'Movie');
        $language = $this->request->get('lang', 'en-us');

        $iptvService = new iptvService($this->request);
        $data = $iptvService->getDimensionData($type, 'area', $language);

        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    public function getByLanguage(): array
    {
        $type = $this->request->get('type', 'Movie');
        $iptvService = new iptvService($this->request);
        $data = $iptvService->getDimensionData($type, 'language');

        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    public function getList(): array
    {
        $ListSearcher = new ListSearcher();
        $ListSearcher->cid   = $this->request->get('cid');
        $ListSearcher->genre = $this->request->get('genre');
        $ListSearcher->field = $this->request->get('field', 'hot');
        $ListSearcher->items_page = $this->request->get('items_page', '1');
        $ListSearcher->items_perpage = $this->request->get('items_perpage', '12');

        $iptvService = new iptvService($this->request);
        $data = $iptvService->getList($ListSearcher);

        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }
}