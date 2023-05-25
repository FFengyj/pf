<?php
/**
 * Memcache内存锁适配器类
 */

namespace Pf\System\Lock;


class MemcacheAdapter implements LockInterface {

    /**
     * Memcache对象
     * @var Object(Memcache)
     */
    private $_memcache = NULL;

    private $lock_prefix = '';
    private $lock_timewait = 10000;
    private $lock_timeout = 35;
    private $lock_retry_times = 1000;

    private $lock_key = '';

    /**
     * MemcacheAdapter constructor.
     * @param $cache
     * @param $options
     */
    public function __construct($cache,$options) {

        $this->_memcache = $cache;

        $property = ['lock_prefix','lock_timewait','lock_timeout','lock_retry_times'];
        foreach($property as $attr) {
            if(!isset($options[$attr])) continue;
            $this->$attr = $options[$attr];
        }
    }

    /**
     * 获取内存锁
     *
     * @param  string $key 内存锁去除前缀后的key值
     * @param  int $ttl        锁过期时间
     * @return bool            获取成功返回TRUE
     */
    public function acquire($key,$ttl = 0) {

        $this->lock_key = $this->lock_prefix . $key;
        $ttl = $ttl ?: $this->lock_timeout;

        $i = 0;
        do {

            $lock = $this->_memcache->add( $this->lock_key, 1, $ttl );
            //如果第一次没有获取到锁则等待指定时间后重试
            if ($i > 0) {
                usleep($this->lock_timewait * 1000);
            }
            $i++;
            //超过重试次数后退出
            if ($i > $this->lock_retry_times) {
                return false;
            }
        } while( !$lock );

        return $key;
    }

    /**
     * 释放内存锁
     *
     * @return bool        释放成功返回TRUE
     */
    public function release($lock) {

        return $this->_memcache->delete($lock);
    }

}
?>
