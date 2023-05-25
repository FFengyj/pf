<?php

/**
 * 验证相关的错误码
 */
namespace Pf\System\Errors;

/**
 * Class VerifyCode
 * @package Pf\System\Errors
 * @author fyj
 */
class VerifyCode
{

    const PARAM_NOT_NUMBER                          = 1100;
    const PARAM_NOT_IN_ARRAY                        = 1101;
    const PARAM_TOO_SHORT                           = 1102;
    const PARAM_TOO_LONG                            = 1103;
    const PARAM_NOT_DATE_TIME                       = 1104;
    const PARAM_NOT_JSON_VALUE                      = 1105;
    const PARAM_NOT_MOBILE                          = 1106;
    const MISS_REQUEST_PARAMS                       = 1107;
    const PARAM_IS_EMPTY                            = 1108;
    const PARAM_NOT_PRICE                           = 1109;
    const PARAM_NOT_DIGITS_NUMBER                   = 1110;
    const INVALID_PARAM                             = 1111;
    const PARAM_NOT_EMAIL                           = 1112;
    const PARAM_NOT_ARRAY_VALUE                     = 1113;

    /**
     * 返回给用户的错误信息，包含real_info、show_info、level 等字段
     *
     * real_info必填，表示错误的真实信息，比较敏感，一般不展示给用户
     * show_info可为空，用于展示给用户的信息。若不填则显示real_info中的信息
     * level  错误日志级别，默认为 Logger::WARNING, ERROR 及以上级别会记录log
     *
     * @var array
     */
    public static $MSGS = [


        self::PARAM_NOT_NUMBER => [
            'real_info' => '参数[%s]应该为数字',
        ],
        self::PARAM_NOT_IN_ARRAY => [
            'real_info' => '参数[%s]的值不在正确范围内',
        ],
        self::PARAM_TOO_SHORT => [
            'real_info' => '参数[%s]过短',
        ],
        self::PARAM_TOO_LONG => [
            'real_info' => '参数[%s]过长',
        ],
        self::PARAM_NOT_DATE_TIME => [
            'real_info' => '参数[%s]的日期格式不正确',
        ],
        self::PARAM_NOT_JSON_VALUE => [
            'real_info' => '参数[%s]的值不是json字符串',
        ],
        self::PARAM_NOT_MOBILE => [
            'real_info' => '参数[%s]的值必须为手机号',
        ],
        self::MISS_REQUEST_PARAMS => [
            'real_info' => '请求缺少参数：%s',
        ],
        self::PARAM_IS_EMPTY => [
            'real_info' => '参数[%s]的值不为空',
        ],
        self::PARAM_NOT_PRICE => [
            'real_info' => '参数[%s]不是有效金额',
        ],
        self::PARAM_NOT_DIGITS_NUMBER => [
            'real_info' => '参数[%s]必须为正整数',
        ],
        self::INVALID_PARAM => [
            'real_info' => '参数不正确',
        ],
        self::PARAM_NOT_EMAIL => [
            'real_info' => '参数[%s]的值必须为邮箱'
        ],
        self::PARAM_NOT_ARRAY_VALUE => [
            'real_info' => '参数[%s]的值不是数组',
        ],
    ];

}
