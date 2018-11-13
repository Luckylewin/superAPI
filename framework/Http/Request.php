<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/8/31
 * Time: 14:25
 */

namespace Breeze\Http;


class Request
{
    /**
     * get data
     * @var object
     */
    protected $_get;

    /**
     * post data
     * @var object
     */
    protected $_post;

    /**
     * request data
     * @var object
     */
    protected $_request;

    /**
     * raw data
     * @var object
     */
    protected $_rawData;

    protected $_rawPost;

    /**
     * cookie data
     * @var object
     */
    protected $_cookie;

    /**
     * server info
     * @var object
     */
    protected $_server;

    /**
     * @var
     */
    protected static $_clientIP;

    public $isGet;

    public $isPost;

    public function __construct()
    {
        $this->_get     = (object) $_GET;
        $this->_post    = (object) $_POST;
        $this->_request = (object) $_REQUEST;
        $this->_cookie  = (object) $_COOKIE;
        $this->_server  = (object) $_SERVER;
        $this->_rawData = (object) $GLOBALS['HTTP_RAW_POST_DATA'];
        $this->isGet    = $this->_server->REQUEST_METHOD == 'GET'  ? true : false;
        $this->isPost   = $this->_server->REQUEST_METHOD == 'POST' ? true : false;
    }

    /**
     * Get an input element from the request.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        if (isset($this->_request->$key)) {
            return $this->_request->$key;
        }

        return null;
    }

    /**
     * @param null $key
     * @return null|object
     */
    public function get($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->_get;
        } else if (isset($this->_get->$key)) {
            return $this->_get->$key;
        } else {
            return $default;
        }
    }

    public function setGet($get)
    {
        return $this->_get = $get;
    }

    public function redirect($uri)
    {
        $this->_server->REQUEST_URI = $uri;
    }

    public function post($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->_post;
        } else if (isset($this->_post->$key)) {
            return $this->_post->$key;
        } else {
            return $default;
        }
    }

    public function setPost($data)
    {
        $this->_post = (object) $data;
    }

    public function request()
    {
        return $this->_request;
    }

    public function setRequest($data)
    {
        $this->_request = (object) $data;
    }

    public function server($key = null)
    {
        if (is_null($key)) {
            return $this->_server;
        } else if (isset($this->_server->$key)) {
            return $this->_server->$key;
        } else {
            return null;
        }
    }

    public function rawData()
    {
        return $this->_rawData;
    }

    public function method()
    {
        return $this->server()->REQUEST_METHOD;
    }

    public function path()
    {
        return parse_url($this->server()->REQUEST_URI)['path'];
    }

    public function setIP($ip)
    {
        static::$_clientIP = $ip;
    }

    public function ip()
    {
        return self::$_clientIP;
    }
}