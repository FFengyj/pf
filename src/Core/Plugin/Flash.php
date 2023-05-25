<?php
/**
 * 自定义的输出信息类
 */

namespace Pf\System\Core\Plugin;

use Phalcon\Di;
use Phalcon\Flash\Direct;
use Phalcon\Http\Response;

/**
 * Class Flash
 * @package Pf\System\Core\Plugin
 * @author fyj
 */
class Flash extends Direct
{

    /**
     * 返回成功信息
     *
     * @param array  $data
     * @param int    $code
     * @param string $msg
     */
    public function successJson($data = [], $code = 0, $msg = 'ok')
    {

        // 设置返回头
        $response = new Response();
        $response->setContentType('application/json');

        if (empty($data)) {
            $data = new \stdClass();
        }

        // 返回信息
        $message = json_encode(
            [
                'code' => intval($code),
                'msg'    => $msg,
                'data'   => $data,
            ], JSON_UNESCAPED_UNICODE
        );
        $response->send();


//        // 关闭模板渲染
        if (Di::getDefault()->has('view')) {
            Di('view')->disable();
        }

        $this->setAutomaticHtml(false);
        $this->setAutoescape(false);
        $this->success($message);
    }

    /**
     * 返回错误信息
     *
     * @param       $ret
     * @param       $msg
     * @param array $data
     */
    public function errorJson($ret, $msg, $data = [])
    {
        // 设置返回头
        $response = new Response();
        $response->setContentType('application/json', 'UTF-8');

        if (empty($data)) {
            $data = new \stdClass();
        }
        $code = intval($ret) ? intval($ret) : -1;

        // 返回信息
        $message = json_encode(
            [
                'code' => $code,
                'msg'    => $msg,
                'data'   => $data,
            ], JSON_UNESCAPED_UNICODE
        );

        $response->send();

        // 关闭模板渲染
        if (Di::getDefault()->has('view')) {
            Di('view')->disable();
        }
        $this->setAutomaticHtml(false);
        $this->setAutoescape(false);
        $this->error($message);
    }


}
