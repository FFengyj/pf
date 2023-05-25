<?php

/**
 * 校验：检测值是否在给定的数组中
 */

namespace Pf\System\Verify\Rules;


use Pf\System\Errors\VerifyCode;
use Pf\System\Exceptions\Err\VerifyErrException;
use Pf\System\Verify\VerifyBase;

/**
 * Class IsArrayVerify
 * @package Pf\System\Verify\Rules
 * @author fyj
 */
class IsArrayVerify extends VerifyBase
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
            if (!isset($data[$k])) {
                continue;
            }

            $key = $k;
            if(is_numeric($k)){
                $key = $v;
            }
            if(!isset($data[$key])){
                continue;
            }

            $msg =  !is_numeric($k) ? $v :"";
            if(!is_array($data[$key])){
                throw new VerifyErrException(VerifyCode::PARAM_NOT_ARRAY_VALUE,[$key],$msg);
            }
        }
    }
}