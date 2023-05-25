<?php

/**
 * 未捕获的异常处理
 */
namespace Pf\System\Exceptions\Handler;

/**
 * Class UnexpectedHandler
 * @package Pf\System\Exceptions\Handler
 * @author fyj
 */
class UnexpectedHandler implements HandlerInterface
{

    /**
     * @param \Throwable $e
     * @throws \Throwable
     */
    public function handler(\Throwable $e)
    {
        Di('logger')->error('unexpected',[$e]);
        if (php_sapi_name() != 'cli') {
            if (env('DISPLAY_ERRORS',false)) {
                throw $e;
            }
            Di('flash')->errorJson(-1,"系统开小差了，请稍等片刻");
        } else {
            Di('logger.stdout')->error('unexpected',[$e]);
        }
    }
}
