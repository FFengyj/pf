<?php

namespace Pf\System\Core\Logger\Formatter;

use Phalcon\Logger as Logger;
use Phalcon\Logger\FormatterInterface;

/**
 * Class CustomJson
 * @package Pf\System\Core\Logger\Formatter
 * @author fyj
 */
class CustomJson implements FormatterInterface
{

    protected $ip_proxy = true;

    /**
     * 记录请求的Header信息
     * @var array
     */
    protected $headers = [];

    /**
     * @param $bool
     * @return $this
     */
    public function setIpProxy(bool $bool)
    {
        $this->ip_proxy = $bool;
        return $this;
    }

    /**
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Applies a format to a message before sent it to the internal log
     *
     * @param string $message
     * @param int $type
     * @param int $timestamp
     * @param array $context
     * @return string|array
     */
    public function format($message, $type, $timestamp, $context = null)
    {

        $server_addr = $_SERVER['SERVER_ADDR'] ?? (getenv('HOSTNAME') ?: '');

        if (php_sapi_name() == 'cli') {
            $headers = [];
        } else{
            $headers = [];
            foreach($this->headers as $key) {
                $headers[$key] = Di('request')->getHeader($key);
            }
            $headers = http_build_query($headers);
        }

        return json_encode([
                '@timestamp'    => date(\DateTime::ISO8601,$timestamp),
                'level'         => $this->getTypeString($type),
                'server_addr'   => $server_addr,
                'remote_addr'   => client_ip($this->ip_proxy),
                'headers'       => $headers,
                'method'        => empty($_SERVER['REQUEST_METHOD']) ? '' : $_SERVER['REQUEST_METHOD'],
                'domain'        => empty($_SERVER['HTTP_HOST']) ? '' : $_SERVER['HTTP_HOST'],
                'referer'       => empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER'],
                'agent'         => empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'],
                'request'       => empty($_REQUEST) ? '' : http_build_query($_REQUEST),
                'uri'           => empty($_SERVER['REQUEST_URI']) ? '' : $_SERVER['REQUEST_URI'],
                'message'       => $message,
                'contexts'      => $this->formatContext($context),
        ],JSON_UNESCAPED_UNICODE) . "\n";
    }


    /**
     * Translates Phalcon log types into log level strings.
     *
     * @param  integer $type
     * @return string
     */
    protected function getTypeString($type)
    {
        switch ($type) {
            case Logger::EMERGENCY:
            case Logger::EMERGENCE:
            case Logger::CRITICAL:
                // emergence, critical
                return 'crit';

            case Logger::ALERT:
            case Logger::ERROR:
                // error, alert
                return 'error';

            case Logger::WARNING:
                // warning
                return 'warning';

            case Logger::NOTICE:
            case Logger::INFO:
                // info, notice
                return 'info';

            case Logger::DEBUG:
            case Logger::CUSTOM:
            case Logger::SPECIAL:
            default:
                // debug, log, custom, special
                return 'debug';
        }
    }

    /**
     * @param $context
     * @return mixed|string
     */
    protected function formatContext($context)
    {

        $msg_func = function ($mixed) {
            if ($mixed instanceof \Throwable) {
                // 记录错误信息
                $msg = "[".get_class($mixed)."] Error Code: " . $mixed->getCode() . " Msg:".$mixed->getMessage()." \n";
                $msg .= "File:" . $mixed->getFile() . "(".$mixed->getLine().")\n";
                $msg .= "Trace: \n" . $mixed->getTraceAsString();
            }else if (!is_string($mixed)) {
                $msg = @var_export($mixed, true);
            } else {
                $msg = $mixed;
            }
            return $msg;
        };

        if (is_scalar($context)) {
            return $context;
        } elseif (is_array($context)) {
            $msg = '';
            foreach($context as $k => $v) {
                $msg .= $k .":". $msg_func($v) . "\n";
            }
            return $msg;
        }
        return '';
    }
}