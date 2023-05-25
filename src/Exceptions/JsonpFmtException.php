<?php

/**
 * 业务异常类，异常结果使用JSONP展示
 */

namespace Pf\System\Exceptions;

/**
 * Class JsonpFmtException
 * @package Pf\System\Exceptions
 * @author fyj
 */
class JsonpFmtException extends \Exception
{
    protected $data = array();

    /**
     * 初始化异常
     *
     * @param string $message
     * @param int $code
     * @param array $data
     * @param \Exception $previous
     */
    public function __construct($message, $code, $data = array(), \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getCallback()
    {
        return  !empty($this->data['callback']) ? $this->data['callback'] : "callback";
    }

}