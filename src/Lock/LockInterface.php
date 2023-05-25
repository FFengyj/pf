<?php
/**
 * 内存锁接口
 *
 * User: julianhu
 * Date: 15/8/5
 * Time: 23:01
 */

namespace Pf\System\Lock;


interface LockInterface {

    public function acquire($key,$ttl = 0);

    public function release($lock);

} 