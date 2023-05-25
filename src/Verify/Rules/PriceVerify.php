<?php

/**
 * 校验：检测值是否为价格
 */

namespace Pf\System\Verify\Rules;

use Pf\System\Errors\VerifyCode;
use Pf\System\Exceptions\Err\VerifyErrException;
use Pf\System\Verify\VerifyBase;

/**
 * Class PriceVerify
 * @package Pf\System\Verify\Rules
 * @author fyj
 */
class PriceVerify extends VerifyBase
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

            if (isset($data[$key]) && (!is_numeric($data[$key]) || (!preg_match('/^[0-9]+\.\d{2}$/',$data[$key]) && $data[$key] != 0))) {
                throw new VerifyErrException(VerifyCode::PARAM_NOT_PRICE,[$key],$msg);
            }
        }
    }

}