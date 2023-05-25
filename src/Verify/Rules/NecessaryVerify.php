<?php

/**
 * 校验：检测是否传了指定字段，字段值可为0或空字符串
 */

namespace Pf\System\Verify\Rules;


use Pf\System\Errors\VerifyCode;
use Pf\System\Exceptions\Err\VerifyErrException;
use Pf\System\Verify\VerifyBase;

/**
 * Class NecessaryVerify
 * @package Pf\System\Verify\Rules
 * @author fyj
 */
class NecessaryVerify extends VerifyBase
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

            // 索引数组
            $msg = '';
            if (is_numeric($k)) {
                $key  = $v;
            } else {
                $key  = $k;
                $msg = $v;
            }
            // 只判断是否有该字段，值可为0或空串
            if (!isset($data[$key])) {
                throw new VerifyErrException(VerifyCode::MISS_REQUEST_PARAMS,[$k],$msg);
            }
        }
    }

}