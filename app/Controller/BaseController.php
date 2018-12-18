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
        $this->error = $errorCode;
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

        $this->uid = $request->post('uid','')?:$request->post('mac','');
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

        $fieldValue = $this->getFieldValue($field, $default);

        if (!is_null($rule)) {
            $result =  Validator::validate($rule, $fieldValue);
            if ($result['status'] === false) {
                throw new \InvalidArgumentException("参数错误", $result['code']);
            }

            return $result['value'];
        }

        if ($fieldValue) {
            return $fieldValue;
        } else if(!is_null($default)) {
            return $default;
        } else {
            return null;
        }
    }

    protected function getFieldValue($field, $default)
    {
        $val = '';

        if ($this->request->post('header')) {
            if (!isset($this->data[$field]) && is_null($default)) {
                throw new \InvalidArgumentException($field . "是必须的参数", ErrorCode::$RES_ERROR_PARAMETER_MISSING);
            }
            if (isset($this->data[$field])) {
                $val = $this->data[$field];
            }
        } else {
            $val = $this->request->post($field,'');
        }

        return $val;
    }

    public function fail($code, $mode='normal')
    {
        $this->setError($code);
        if ($mode == 'normal') {
            return Formatter::response($code);
        }

        return Formatter::back([],$code);
    }

    public function success($data)
    {
        return Formatter::success($data);
    }

}
