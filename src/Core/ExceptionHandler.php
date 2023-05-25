<?php

namespace Pf\System\Core;


use Pf\System\Exceptions\Err\CustomErrException;
use Pf\System\Exceptions\Handler\HandlerInterface;
use Pf\System\Exceptions\Handler\UnexpectedHandler;
use Pf\System\Exceptions\UnexpectedException;
use Phalcon\Logger;

/**
 * Class ExceptionHandler
 * @package Pf\System\Core
 * @author fyj
 */
class ExceptionHandler
{

    protected $handlers = [];

    /**
     * ExceptionHandler constructor.
     * @param $handlers
     */
    public function __construct($handlers)
    {
        $this->handlers = $handlers;
    }

    /**
     * @param \Throwable $e
     * @param bool $is_previous
     */
    public function handler(\Throwable $e,$is_previous = false)
    {

        if ($previous = $e->getPrevious()) {
             $this->handler($previous,true);
        }

        $class = get_class($e);

        if (isset($this->handlers[$class])) {

            $obj = new $this->handlers[$class]($e);
            if(method_exists($obj,'handler')) {
                $obj->handler($e);
            }

        } elseif ( $e instanceof CustomErrException) {

            if ($e->getErrLevel() <= Logger::ERROR) {
                $info = "Code:" . $e->getCode() . "\n";
                $info .= "Info:" . $e->getRealInfo() . "\n";
                $info .= "File:" . $e->getFile() . "(".$e->getLine().")\n";

                Di('logger')->log($e->getErrLevel(),get_class($e),[$info]);
                if (php_sapi_name() == 'cli') {
                    Di('logger.stdout')->log($e->getErrLevel(),$e->getShowInfo(),$e->getData());
                }
            }

            if (!$is_previous && php_sapi_name() != 'cli') {
                Di('flash')->errorJson($e->getCode(),$e->getShowInfo(),$e->getData());
            }

        }else {
            $unexpected_class = $this->handlers[UnexpectedException::class] ?? UnexpectedHandler::class;
            /* @var HandlerInterface $obj */
            $obj = new $unexpected_class();
            $obj->handler($e);
        }

    }

    /**
     * @param array $handlers
     * @return callable|null
     */
    public static function init(array $handlers)
    {
        return set_exception_handler([new self($handlers),'handler']);
    }

}
