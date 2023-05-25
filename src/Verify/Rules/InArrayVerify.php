<?php

/**
 * 校验：检测值是否在给定的数组中
 */

namespace Pf\System\Verify\Rules;


use Pf\System\Errors\VerifyCode;
use Pf\System\Exceptions\Err\VerifyErrException;
use Pf\System\Verify\VerifyBase;

/**
 * Class InArrayVerify
 * @package Pf\System\Verify\Rules
 * @author fyj
 */
class InArrayVerify extends VerifyBase
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

            // 给定的配置不是数组的情况
            if (!is_array($v['array']) ) {
                $arr = call(explode('::', $v['array']), []);
            } else {
                $arr = $v['array'];
            }

            $msg = !empty($v['err_msg']) ? $v['err_msg'] : "";

            // 判定
            if (!in_array($data[$k], $arr)) {
                throw new VerifyErrException(VerifyCode::PARAM_NOT_IN_ARRAY,[$k],$msg);
            }
        }
    }
}