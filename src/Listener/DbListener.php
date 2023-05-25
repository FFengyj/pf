<?php
/**
 * Db事件监听类
 */

namespace Pf\System\Listener;

use Pf\System\Core\Mvc\ModelBase;

/**
 * Class DbListener
 * @package Pf\System\Listener
 * @author fyj
 */
class DbListener
{

    /**
     * 数据库超时重连次数
     *
     * @var int
     */
    protected $retry_times = 5;

    /**
     * 处理 'beforeQuery' 事件
     */
    public function beforeQuery($event, $connection)
    {
        $i = 0;
        do {
            try {
                // 注：重连后PDO对象已经重新生成

                /* @var \Pdo $pdo*/
                $pdo = $connection->getInternalHandler();
                @$pdo->getAttribute(\PDO::ATTR_SERVER_INFO);

            } catch (\PDOException $e) {

                // 获取错误信息并记录
                $error_code = (int)$e->errorInfo[1];
                $error_msg  = $e->errorInfo[2];
                Di('logger')->error("MysqlError,Before Query Error: [{$error_code}] {$error_msg}");

                // 重连数据库
                if ($error_code == 2006 && $error_msg == 'MySQL server has gone away') {
                    $connection->connect();
                    $i++;
                    Di('logger')->error("MysqlError, 重试次数：" . $i);
                    continue;
                }
            }
            break;
            // 最多重试5次
        } while ($i < $this->retry_times);
    }


    /**
     * @param \Phalcon\Events\Event $event
     * @param $connection
     */
    public function commitTransaction(\Phalcon\Events\Event $event,$connection)
    {
        foreach (ModelBase::getDelayModelEvents(true) as $model_event) {
            [$callback,$args] = $model_event;
            call_user_func_array($callback,array_merge($args,[$event->getType()]));
        }
    }

}