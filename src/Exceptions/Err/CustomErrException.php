<?php
/**
 * 自定义错误异常基础类
 */

namespace Pf\System\Exceptions\Err;

use Phalcon\Logger;
use Throwable;

/**
 * Class CustomErrException
 * @package Pf\System\Exceptions\Err
 * @author fyj
 */
abstract class CustomErrException extends \Exception
{

    /**
     * message 中所需替换的变量
     * @var array
     */
    protected $variables;

    /**
     * @var array
     */
    protected $data;

    /**
     * 错误码配置
     * @var array|mixed
     */
    protected $settings = [];


    /**
     * @return mixed
     */
    abstract protected function getErrCodeSettings();

    /**
     * VerifyException constructor.
     * @param int $code
     * @param array $variables
     * @param string $msg
     * @param array $data
     * @param Throwable|null $previous
     */
    public function __construct($code = 0,$variables = [], $msg = '', $data = [],Throwable $previous = null)
    {
        $this->variables = $variables;
        $this->data = $data;
        $this->settings = $this->getErrCodeSettings()[$code] ?? [];

        $msg = $msg ? asprintf($msg,$this->variables) : $this->getShowInfo();
        parent::__construct($msg, $code, $previous);
    }

    /**
     * @return int
     */
    public function getErrLevel(): int
    {
        return $this->settings['level'] ?? Logger::WARNING;
    }

    /**
     * @return string
     */
    public function getRealInfo(): string
    {
        $real_info = $this->settings['real_info'] ?? '未知错误';
        return asprintf($real_info,$this->variables);
    }

    /**
     * @return string
     */
    public function getShowInfo(): string
    {
        $show_info = $this->settings['show_info'] ?? '';
        if ($show_info) {
            $show_info = asprintf($show_info,$this->variables);
        } else {
            $show_info = $this->getRealInfo();
        }
        return $show_info;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

}