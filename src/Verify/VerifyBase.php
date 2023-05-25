<?php

/**
 * 校验抽象类
 */

namespace Pf\System\Verify;


use Pf\System\Exceptions\Err\VerifyErrException;

/**
 * Class VerifyBase
 * @package Pf\System\Verify
 * @author fyj
 */
abstract class VerifyBase
{
    /**
     * 校验参数
     *
     * @param $data   array 待校验的数据
     * @param $fields array 需要校验的参数列表
     * @throws VerifyErrException
     */
    abstract public function verify($data, $fields);

}