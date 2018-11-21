<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/20
 * Time: 14:38
 */

namespace App\Controller;

use App\Components\http\Formatter;
use App\Service\MemberService;
use App\Exceptions\ErrorCode;

/**
 * 会员
 * Class MemberController
 * @package App\Controller
 */
class MemberController extends BaseController
{
    public function getPrice(): array
    {
        $iptvService = new MemberService($this->request);
        $data = $iptvService->getPrice();
        if ($data['status'] === false) {
            return Formatter::back('',$data['code']);
        }

        return Formatter::back($data['data'], ErrorCode::$RES_SUCCESS);
    }
}