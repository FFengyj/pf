<?php

/**
 * 校验类型
 */

namespace Pf\System\Verify;


use Pf\System\Verify\Rules\LengthLimitVerify;
use Pf\System\Verify\Rules\DigitsVerify;
use Pf\System\Verify\Rules\InArrayVerify;
use Pf\System\Verify\Rules\IsArrayVerify;
use Pf\System\Verify\Rules\IsDateVerify;
use Pf\System\Verify\Rules\IsEmailVerify;
use Pf\System\Verify\Rules\IsJsonVerify;
use Pf\System\Verify\Rules\IsMobileVerify;
use Pf\System\Verify\Rules\NecessaryVerify;
use Pf\System\Verify\Rules\NotNullVerify;
use Pf\System\Verify\Rules\NumericVerify;
use Pf\System\Verify\Rules\PriceVerify;

/**
 * Class VerifyType
 * @package Pf\System\Verify
 * @author fyj
 */
class VerifyType
{
    /**
     * 参数不能缺少的情况
     */
    const NECESSARY = NecessaryVerify::class;

    /**
     * 参数必须为数字的情况
     */
    const NUMERIC = NumericVerify::class;

    /**
     * 字符串长度限制
     */
    const LENGTH_LIMIT = LengthLimitVerify::class;

    /**
     * 参数是否在指定的数组集中
     */
    const IN_ARRAY = InArrayVerify::class;

    /**
     * 判断参数是否为json
     */
    const IS_JSON = IsJsonVerify::class;

    /**
     * 验证参数是否为空 ['',null,' ']
     */
    const NOT_NULL = NotNullVerify::class;


    /**
     * 验证是否是正整数
     */
    const DIGITS = DigitsVerify::class;

    /**
     * 价格验证
     */
    const PRICE = PriceVerify::class;

    /**
     * 验证是否是一个日期
     */
    const IS_DATE = IsDateVerify::class;

    /**
     * 验证是否是手机号
     */
    const IS_MOBILE = IsMobileVerify::class;

    /**
     * 验证是否是邮箱
     */
    const IS_EMAIL = IsEmailVerify::class;

    /**
     * 验证是否是数组
     */
    const IS_ARRAY = IsArrayVerify::class;


}