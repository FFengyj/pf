<?php

namespace Pf\System\Core\Logger;


use Pf\System\Core\Logger\Formatter\CustomJson;
use Phalcon\Logger\Adapter\File;

/**
 * Class Custom
 * @package Pf\System\Core\Logger
 * @author fyj
 */
class Custom extends File
{

    /**
     * Custom constructor.
     * @param $params
     */
    public function __construct($params)
    {
        if (!is_dir($params['path'])) {
            mkdir($params['path'], 0777, true);
        }

        parent::__construct(rtrim($params['path'],"/") . "/" . $params['filename']);

        $format = new CustomJson();
        $format->setHeaders(['X-Live-User-Id','X-Real-Ip','REQUEST_TIME_FLOAT']);

        $this->setFormatter($format);

        if (isset($params['level'])) {
            $this->setLogLevel($params['level']);
        }

    }

}