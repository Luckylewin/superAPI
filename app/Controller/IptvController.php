<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/19
 * Time: 9:51
 */

namespace App\Controller;

use App\Exceptions\ErrorCode;
use App\Service\authService;
use App\Components\http\Formatter;
use App\Service\iptvService;

class IptvController extends BaseController
{
    public function auth()
    {
        $authService = new authService($this->request);
        $token = $authService->login();
        if ($token['status'] === false) {
            return Formatter::response($token['code']);
        }

        return Formatter::success($token['data']);
    }

    public function getBanner()
    {
        $iptvService = new iptvService($this->request);
        $data = $iptvService->getBanner();
        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    public function getType()
    {
        $iptvService = new iptvService($this->request);
        $data = $iptvService->getType();
        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    public function getVods()
    {
        $iptvService = new iptvService($this->request);
        $data = $iptvService->getVods();
        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    public function getVod($id)
    {
        $iptvService = new iptvService($this->request);
        $data = $iptvService->getVod($id);
        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    public function getVodLinks($vod_id)
    {
        $iptvService = new iptvService($this->request);
        $data = $iptvService->getVodLinks();
        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }

    public function getRecommends($id)
    {
        $iptvService = new iptvService($this->request);
        $data = $iptvService->getRecommends($id);
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
        $iptvService = new iptvService($this->request);
        $data = $iptvService->getCondition();
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

}