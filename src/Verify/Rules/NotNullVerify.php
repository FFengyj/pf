<?php

/**
 * 校验：检测值是否是空值
 */

namespace Pf\System\Verify\Rules;



use Pf\System\Errors\VerifyCode;
use Pf\System\Exceptions\Err\VerifyErrException;
use Pf\System\Verify\VerifyBase;

/**
 * Class NotNullVerify
 * @package Pf\System\Verify\Rules
 * @author fyj
 */
class NotNullVerify extends VerifyBase
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
            $msg =  !is_numeric($k) ? $v :"";

            if(!isset($data[$key])){
                throw new VerifyErrException(VerifyCode::PARAM_IS_EMPTY,[$key],$msg);
            }

            if(is_array($data[$key])){
                if(count($data[$key]) < 1){
                    throw new VerifyErrException(VerifyCode::PARAM_IS_EMPTY,[$key],$msg);
                }

            // check values ['',null,'  ']
            }else if(trim($data[$key]) == ''){
                throw new VerifyErrException(VerifyCode::PARAM_IS_EMPTY,[$key],$msg);
            }
        }
    }

}