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
     * @return string
     */
    public function auth()
    {
        $mac       = $this->request->post('mac');
        $timestamp = $this->request->post('timestamp');
        $signature = $this->request->post('signature');

        $authService = new authService($this->request);
        $token = $authService->login($mac, $timestamp, $signature);
        if ($token['status'] === false) {
            return Formatter::response($token['code']);
        }

        return Formatter::success($token['data']);
    }

    /**
     * 获取 banner 图
     * @return mixed
     */
    public function getBanner()
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
    public function getType()
    {
        $iptvService = new iptvService($this->request);
        $expand = $this->request->get('expand');
        $data   = $iptvService->getType($expand);
        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    public function getVods()
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

        $iptvService = new iptvService($this->request);
        $data = $iptvService->getVods($searcher);
        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    public function getVod($id)
    {
        $expand = $this->request->get('expand', false);
        $iptvService = new iptvService($this->request);
        $data = $iptvService->getVod($id, $expand);
        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    public function getVodLinks($vod_id)
    {
        $iptvService = new iptvService($this->request);
        $data = $iptvService->getVodLinks($vod_id);
        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    public function getRecommends($id)
    {
        $num = $this->request->get('num', 4);
        $iptvService = new iptvService($this->request);
        $data = $iptvService->getRecommends($id, $num);
        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    public function vodHome()
    {
        $iptvService = new iptvService($this->request);
        $data = $iptvService->vodHome();
        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    public function getCondition()
    {
        $list_id = $this->request->get('vod_id');
        $iptvService = new iptvService($this->request);
        $data = $iptvService->getCondition($list_id);
        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    public function getLink($id)
    {
        $iptvService = new iptvService($this->request);
        $data = $iptvService->getLink($id);
        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    public function getCategory()
    {
        $type = $this->request->get('type', 'Movie');
        $iptvService = new iptvService($this->request);
        $data = $iptvService->getCategory($type);

        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    public function getByHot()
    {
        $type = $this->request->get('type', 'Movie');
        $iptvService = new iptvService($this->request);
        $data = $iptvService->getDimensionData($type, 'hot');

        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    public function getByType()
    {
        $type = $this->request->get('type', 'Movie');
        $iptvService = new iptvService($this->request);
        $data = $iptvService->getDimensionData($type, 'type');

        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    public function getByYear()
    {
        $type = $this->request->get('type', 'Movie');
        $iptvService = new iptvService($this->request);
        $data = $iptvService->getDimensionData($type, 'year');

        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    public function getByArea()
    {
        $type = $this->request->get('type', 'Movie');
        $iptvService = new iptvService($this->request);
        $data = $iptvService->getDimensionData($type, 'area');

        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    public function getByLanguage()
    {
        $type = $this->request->get('type', 'Movie');
        $iptvService = new iptvService($this->request);
        $data = $iptvService->getDimensionData($type, 'language');

        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    public function getList()
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