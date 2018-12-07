<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/9/4
 * Time: 17:45
 */

namespace App\Controller;


use App\Components\http\Formatter;
use App\Components\Log;
use App\Components\Validator;
use App\Exceptions\ErrorCode;
use Breeze\Http\Controller;
use Breeze\Http\Request;

class BaseController extends Controller
{
    public $request;
    public $data;
    public $uid;
    public $error;

    public function setError($errorCode)
    {
        $this->error = ErrorCode::getError($errorCode);
    }

    public function __destruct()
    {
        // 记录日志
        Log::info($this->request, $this->error);
    }

    /**
     * BaseController constructor.
     * @param $request
     */
    public function __construct(Request $request)
    {
        // 兼容非标准json post数据
        if (!isset($request->server()->HTTP_CONTENT_TYPE) || $request->server()->HTTP_CONTENT_TYPE != 'application/json') {
            $post = json_decode($request->rawData()->scalar, true);
            $request->setPost($post);
            $request->setRequest($post);
        }

        $this->request = $request;
        $header = $this->request->post()->header ?? 'header';
        Formatter::setHeader($header);



        $this->uid = $request->post('uid');
        $this->data = $request->post('data');
    }

    /**
     * 处理raw post数据
     * @param null $field
     * @param null $default
     * @param null $rule
     * @return null|object|string|integer
     * @throws \Exception
     */
    public function post($field = null, $default = null, $rule = null)
    {
        if (!$field && !$default) {
            return $this->data;
        }
        if (!isset($this->data[$field]) && is_null($default)) {
            throw new \InvalidArgumentException($field . "是必须的参数", ErrorCode::$RES_ERROR_PARAMETER_MISSING);
        }

        if (isset($this->data[$field])) {
            $val = $this->data[$field];
            if ($rule) {
                $result =  Validator::validate($rule, $val);
                if ($result['status'] === false) {
                    throw new \InvalidArgumentException("参数错误", $result['code']);
                }
                return $result['value'];
            }
            return $val;

        } else if ($default) {
            return $val = $default;
        }

        return null;
    }

    public function fail($code)
    {
        $this->setError($code);
        return Formatter::response($code);
    }

    public function success($data)
    {
        return Formatter::success($data);
    }

}
