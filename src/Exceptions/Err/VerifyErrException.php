<?php

/**
 * 校验异常类
 */

namespace Pf\System\Exceptions\Err;

use Pf\System\Errors\VerifyCode;

/**
 * Class VerifyErrException
 * @package Pf\System\Exceptions\Err
 * @author fyj
 */
class VerifyErrException extends CustomErrException
{
    /**
     * @inheritDoc
     * @return array|mixed
     */
    protected function getErrCodeSettings()
    {
        return VerifyCode::$MSGS;
    }

}