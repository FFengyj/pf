<?php

/**
 * 校验：检测字符串长度是否在限定范围内
 */

namespace Pf\System\Verify\Rules;



use Pf\System\Errors\VerifyCode;
use Pf\System\Exceptions\Err\VerifyErrException;
use Pf\System\Verify\VerifyBase;

/**
 * Class LengthLimitVerify
 * @package App\Library\Verify\Rules
 * @author fyj
 */
class LengthLimitVerify extends VerifyBase
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

            if(!isset($data[$k])){
                continue;
            }

            $len = mb_strlen($data[$k], 'utf8');

            if ( isset($v['min']) && $len < $v['min'] ) {
                $msg = !empty($v['min_err_msg']) ? $v['min_err_msg'] : "";
                throw new VerifyErrException(VerifyCode::PARAM_TOO_SHORT,[$k],$msg);
            }

            if ( isset($v['max']) && $len > $v['max'] ) {
                $msg = !empty($v['max_err_msg']) ? $v['max_err_msg'] : "";
                throw new VerifyErrException(VerifyCode::PARAM_TOO_LONG,[$k],$msg);
            }
        }
    }

}