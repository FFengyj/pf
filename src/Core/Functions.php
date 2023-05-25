<?php

use Phalcon\Events\Manager;
use Phalcon\Flash;

if (!function_exists('Di')) {

    /**
     * @param $service
     * @param bool $is_share
     * @return mixed
     */
    function Di($service,$is_share = true)
    {
        return \Phalcon\Di::getDefault()->get($service,$is_share);
    }
}

if (!function_exists('config_merge')) {

    /**
     * 配置文件合并
     *
     * @param $arr1
     * @param $arr2
     * @return mixed
     */
    function config_merge($arr1, $arr2)
    {
        foreach($arr2 as $key => $val)
        {
            if(array_key_exists($key, $arr1) && is_array($val)){
                if(array_keys($val) === range(0, count($val) - 1)){
                    $arr1[$key] = $arr2[$key];
                } else {
                    $arr1[$key] = config_merge($arr1[$key], $arr2[$key]);
                }
            } else {
                $arr1[$key] = $val;
            }
        }
        return $arr1;
    }

}

if (!function_exists('attach_events')) {

    /**
     * @param $events
     * @return Manager|null
     */
    function attach_events($events)
    {
        $em = null;
        if ($events) {

            $em = null;
            foreach ($events as $e) {

                if ($e['listener'] instanceof \Closure) {
                    $em = $em ?: new Manager();
                    $em->attach($e['attach'], $e['listener'] , $e['priority'] ?? 100);
                } elseif (is_string($e['listener']) && class_exists($e['listener'])) {
                    $em = $em ?: new Manager();
                    $em->attach($e['attach'], new $e['listener']() , $e['priority'] ?? 100);
                }
            }
        }
        return $em;
    }
}

if (! function_exists('call')) {
    /**
     * Call a callback with the arguments.
     *
     * @param mixed $callback
     * @return null|mixed
     */
    function call($callback, array $args = [])
    {
        $result = null;
        if ($callback instanceof \Closure) {
            $result = $callback(...$args);
        } elseif (is_object($callback) || (is_string($callback) && function_exists($callback))) {
            $result = $callback(...$args);
        } elseif (is_array($callback)) {
            [$object, $method] = $callback;
            $result = is_object($object) ? $object->{$method}(...$args) : $object::$method(...$args);
        } else {
            $result = call_user_func_array($callback, $args);
        }
        return $result;
    }
}


if (!function_exists('client_ip')) {

    /**
     * 获取客户端IP
     *
     * @param bool $is_proxy
     * @return string
     */
    function client_ip($is_proxy = true)
    {
        if ($is_proxy) {
            $ip = $_SERVER['HTTP_X_REAL_IP'] ?? '';
            if ($ip && !is_internal_ip($ip)) {
                return $ip;
            }
        }

        $ips[] = $_SERVER['REMOTE_ADDR'] ?? "";
        $ips[] = $_SERVER['HTTP_CLIENT_IP'] ?? "";

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips[] = trim(explode(",",$_SERVER['HTTP_X_FORWARDED_FOR'])[0] ?? "");
        }

        foreach ($ips as $ip) {
            if ($ip && !is_internal_ip($ip)) {
                return $ip;
            }
        }

        return $ips[0] ?? '';
    }
}


function is_internal_ip($ip)
{
    return in_array(substr($ip, 0, strpos($ip, '.')), ['127', '10', '172', '192']);
}


if (!function_exists('asprintf')) {

    function asprintf(string $format , array $values)
    {
        $values = array_pad($values,substr_count($format,'%'),'');
        return vsprintf($format,$values);
    }
}

if (!function_exists('get_task_params')) {

    function get_task_params($namespace = '')
    {
        $params = [];
        $args = $_SERVER['argv'] ?? [];
        foreach ($args as $k => $arg) {
            if ($k == 1) {
                $params['task'] = $namespace ? ($namespace . "\\") . $arg : $arg;
            } elseif ($k == 2) {
                $params['action'] = $arg;
            } elseif ($k >= 3) {
                $params['params'][] = $arg;
            }
        }
        return $params;
    }
}

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return $default;
        }

        switch (strtolower($value)) {
            case 'true':
                return true;
            case 'false':
                return false;
            case 'empty':
                return '';
            case 'null':
                return null;
        }

        return $value;
    }
}





