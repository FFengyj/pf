<?php

/**
 * 校验：检测值是否为数字
 */

namespace Pf\System\Verify\Rules;




use Pf\System\Errors\VerifyCode;
use Pf\System\Exceptions\Err\VerifyErrException;
use Pf\System\Verify\VerifyBase;

/**
 * Class NumericVerify
 * @package Pf\System\Verify\Rules
 * @author fyj
 */
class NumericVerify extends VerifyBase
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
        foreach($fields as $k => $v) {

            $key = $k;
            if(is_numeric($k)){
                $key = $v;
            }
            $msg = !is_numeric($k) ? $v : "";

            if (isset($data[$key]) && !is_numeric($data[$key])) {
                throw new VerifyErrException(VerifyCode::PARAM_NOT_NUMBER,[$key],$msg);
            }
        }
    }

}