<?php

/**
 * 业务异常类，异常结果使用JSON展示
 */

namespace Pf\System\Exceptions\Handler;

/**
 * Class JsonFmtHandler
 * @package Pf\System\Exceptions\Handler
 * @author fyj
 */
class JsonFmtHandler implements HandlerInterface
{
    public function handler(\Throwable $e)
    {
        $data = [];
        if (method_exists($e,'getData')) {
            $data = $e->getData();
        }
        Di('flash')->errorJson($e->getCode(),$e->getMessage(),$data);
    }
}