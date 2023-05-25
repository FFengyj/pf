<?php

/**
 * 校验：检测值是否是一个可以转化成时间戳的日期
 */

namespace Pf\System\Verify\Rules;



use Pf\System\Errors\VerifyCode;
use Pf\System\Exceptions\Err\VerifyErrException;
use Pf\System\Verify\VerifyBase;

/**
 * Class IsDateVerify
 * @package Pf\System\Verify\Rules
 * @author fyj
 */
class IsDateVerify extends VerifyBase
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
            if(isset($data[$key]) && strtotime($data[$key]) === false){
                throw new VerifyErrException(VerifyCode::PARAM_NOT_DATE_TIME,[$key],$msg);
            }

        }
    }

}