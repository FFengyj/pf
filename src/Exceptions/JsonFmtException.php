<?php

/**
 * 业务异常类，异常结果使用JSON展示
 */

namespace Pf\System\Exceptions;

class JsonFmtException extends \Exception
{
    protected $data = [];

    /**
     * 初始化异常
     *
     * @param string $message
     * @param int $code
     * @param array $data
     * @param \Throwable $previous
     */
    public function __construct($message, $code, $data = array(), \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

}