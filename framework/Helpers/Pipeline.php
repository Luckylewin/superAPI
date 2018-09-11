<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/8/31
 * Time: 17:17
 */

namespace Breeze\Helpers;


class Pipeline
{
    protected $_pipes;

    public function __construct($pipes)
    {
        foreach ($pipes as $pipe) {
            if (!is_callable($pipe)) {
                throw new \InvalidArgumentException('All pipes should be callable.');
            }
        }

        $this->_pipes = $pipes;
    }

    public function pipe($pipe)
    {
        if (is_callable() === false) {
            throw new InvalidArgumentException('pipe should be callable.');
        }
        $this->_pipes = $pipe;

        return $this;
    }

    public function flow($payload)
    {
        foreach ($this->_pipes as $pipe) {
            $payload = call_user_func($pipe, $payload);

            if ($payload instanceof \Closure) {
                return call_user_func($payload);
            }
        }

        return $payload;
    }
}