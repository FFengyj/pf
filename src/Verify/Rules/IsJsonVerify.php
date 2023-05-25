<?php

/**
 * 校验：检测值是否是json
 */

namespace Pf\System\Verify\Rules;


use Pf\System\Errors\VerifyCode;
use Pf\System\Exceptions\Err\VerifyErrException;
use Pf\System\Verify\VerifyBase;

/**
 * Class IsJsonVerify
 * @package Pf\System\Verify\Rules
 * @author fyj
 */
class IsJsonVerify extends VerifyBase
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
            if(!isset($data[$key])){
                continue;
            }
            $msg =  !is_numeric($k) ? $v :'';

            if(empty($data[$key]) || !is_array(json_decode($data[$key],true))){
                throw new VerifyErrException(VerifyCode::PARAM_NOT_JSON_VALUE,[$key],$msg);
            }

        }
    }

}