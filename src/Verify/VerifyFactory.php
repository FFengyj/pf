<?php
/**
 * 校验工厂类
 */

namespace Pf\System\Verify;

/**
 * Class VerifyFactory
 * @package Pf\System\Verify
 * @author fyj
 */
class VerifyFactory
{
    /**
     * 创建校验类对象
     *
     * @param $rule
     * @return VerifyBase
     */
    public static function create($rule)
    {
        return new $rule();
    }

}