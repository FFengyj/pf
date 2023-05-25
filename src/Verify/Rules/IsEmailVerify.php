<?php

/**
 * 校验：检测值是否是邮箱
 */
namespace Pf\System\Verify\Rules;

use Pf\System\Errors\VerifyCode;
use Pf\System\Exceptions\Err\VerifyErrException;
use Pf\System\Verify\VerifyBase;

/**
 * Class IsEmailVerify
 * @package Pf\System\Verify\Rules
 * @author fyj
 */
class IsEmailVerify extends VerifyBase
{
    /**
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

            $msg = !is_numeric($k) ? $v : '';
            if (!preg_match("/^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/", $data[$key])) {
                throw new VerifyErrException(VerifyCode::PARAM_NOT_EMAIL,[$key],$msg);
            }
        }
    }
}