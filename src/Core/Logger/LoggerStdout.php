<?php

namespace Pf\System\Core\Logger;


use Pf\System\Core\Logger\Formatter\CustomJson;
use Phalcon\Logger\Adapter\Stream;
use Phalcon\Logger\Formatter\Line;

/**
 * Class LoggerStdout
 * @package Logger
 * @author fyj
 */
class LoggerStdout extends Stream
{

    /**
     * Custom constructor.
     * @param $params
     */
    public function __construct($params)
    {

        parent::__construct($params['filename']);

        $format = new CustomJson();
        $this->setFormatter($format);

        if (isset($params['level'])) {
            $this->setLogLevel($params['level']);
        }
    }

}
