<?php
/**
 * 校验：检测值是否为 正整数
 */

namespace Pf\System\Verify\Rules;


use Pf\System\Errors\VerifyCode;
use Pf\System\Exceptions\Err\VerifyErrException;
use Pf\System\Verify\VerifyBase;

/**
 * Class DigitsVerify
 * @package Pf\System\Verify\Rules
 * @author fyj
 */
class DigitsVerify extends VerifyBase
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

            if (isset($data[$key]) && (
                    !is_numeric($data[$key]) ||
                    intval($data[$key]) - abs($data[$key]) != 0 || //是否是小数以及负数
                    count(explode('.',$data[$key])) > 1)) {   //是否是类似 1.00 类似的小数

                throw new VerifyErrException(VerifyCode::PARAM_NOT_DIGITS_NUMBER,[$k],$msg);
            }
        }
    }

}