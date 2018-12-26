<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/12/26
 * Time: 10:03
 */

namespace App\Controller;

use App\Service\ottService;
use App\Service\ParadeService;

class ParadeController extends BaseController
{
    public function index()
    {
        $service = new ParadeService($this->request);

        $parades = $service->getParadeSplitWithDate();

        if ($parades['status'] === false) {
            return $this->fail($parades['code']);
        }

        return $this->success($parades['data']);
    }

    // 获取主要赛事
    public function event()
    {
        try {
            $day = $this->request->get('day', 7);
            $language = $this->request->get('lang', 'en');
            $timezone = $this->request->get('timezone', '+8');
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
}