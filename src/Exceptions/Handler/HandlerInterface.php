<?php

/**
 * 异常Handler接口
 */

namespace Pf\System\Exceptions\Handler;

/**
 * Interface HandlerInterface
 * @package Pf\System\Exceptions\Handler
 * @author fyj
 */
interface HandlerInterface
{
    public function handler(\Throwable $e);
}