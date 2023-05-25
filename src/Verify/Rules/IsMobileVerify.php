<?php

/**
 * 校验：检测值是否是手机号
 */

namespace Pf\System\Verify\Rules;


use Pf\System\Errors\VerifyCode;
use Pf\System\Exceptions\Err\VerifyErrException;
use Pf\System\Verify\VerifyBase;

/**
 * Class IsMobileVerify
 * @package Pf\System\Verify\Rules
 * @author fyj
 */

class IsMobileVerify extends VerifyBase
{
    /**
     * 校验方法
     *
     * @param array $data
     * @param array $fields
     * @throws VerifyErrException
     */
    public function verify($data, $fields)
    {
        foreach ($fields as $k => $v) {

            $key = $k;
            if (is_numeric($k)) {
                $key = $v;
            }
            if (!isset($data[$key])) {
                continue;
            }

            $msg = !is_numeric($k) ? $v : "";
            if (!preg_match("/^1[0-9]{10}$/", $data[$key])) {
                throw new VerifyErrException(VerifyCode::PARAM_NOT_MOBILE,[$key],$msg);
            }
        }
    }

}