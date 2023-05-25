<?php

/**
 * 模型基类
 */
namespace Pf\System\Core\Mvc;


use Constants\RedisKey;
use Phalcon\Mvc\Model as Model;

/**
 * Class ModelBase
 * @package Models\Common
 * @author fyj
 */
class ModelBase extends Model
{
    const UPSET_ENTITY_EVENT_CREATE     =  'afterCreate'; //数据新增事件
    const UPSET_ENTITY_EVENT_UPDATE     =  'afterUpdate'; //数据更新事件
    const UPSET_ENTITY_EVENT_DELETE     =  'afterDelete'; //数据删除事件

    /**
     * 事务提交需执行Model相关的after事件
     * @var array
     */
    protected static $delay_after_events = [];

    /**
     * @var string
     */
    protected static $json_serialize_func;

    /**
     * @var array
     */
    protected $json_serialize = [];

    /**
     * 启用的自动更新缓存的事件
     * @var array
     */
    protected $enabled_cache_events = [
        self::UPSET_ENTITY_EVENT_UPDATE,
        self::UPSET_ENTITY_EVENT_DELETE,
        self::UPSET_ENTITY_EVENT_CREATE
    ];

    /**
     * 初始化时执行
     */
    public function initialize()
    {
        Model::setup(['notNullValidations' => false]);
        $this->useDynamicUpdate(true);
    }

    /**
     * 设置连接句柄
     *
     * @param string $connectionService
     * @return $this
     */
    public function setConnectionService($connectionService)
    {
        parent::setConnectionService($connectionService);
        return $this;
    }

    /**
     * @param string $method
     * @param mixed $arguments
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        $class = static::class;
        if (preg_match('/(.+?)(From|Del|Reset)Cache$/', $method, $match)) {

            $method = $match[1];
            $action = $match[2];

            $key = sprintf('%s::%s', $class, $method);
            $arguments = array_map(function($v){
                return is_numeric($v) ? strval($v) : $v;
            },$arguments);
            $cache_key = sprintf("$key||%s", json_encode($arguments, JSON_UNESCAPED_UNICODE));

            switch ($action) {

                case "From":
                    return self::wrapGetCache($class, $method, $arguments, $cache_key, RedisKey::expire($key));
                case "Del":
                    return self::wrapDelCache($cache_key);
                case "Reset":
                    return self::wrapGetCache($class, $method, $arguments, $cache_key, RedisKey::expire($key), true);
            }
        }

        parent::__callStatic($method, $arguments);
    }

    /**
     * @param $class
     * @param $method
     * @param $arguments
     * @param $cache_key
     * @param $expire
     * @param bool $refresh
     * @return mixed
     */
    private static function wrapGetCache($class, $method, $arguments, $cache_key, $expire, $refresh = false)
    {
        // 查询缓存
        $cache = Di('redis');
        $t = microtime(true);

        if ($refresh || !$cache->exists($cache_key)) {

            $Lock_manager = Di('lock');
            if ($lock = $Lock_manager->acquire($cache_key)) {
                $c = 2;
                do {
                    if ($res = json_decode($cache->get($cache_key), true)) {
                        if ($res['t'] >= $t) {
                            break;
                        }
                    }
                    $model_instance = '';
                    $data = forward_static_call_array([$class, $method], $arguments);
                    if ($data instanceof ModelBase) {
                        $model_instance = $class;
                        $data = $data->toArray();
                    }
                    $res = [
                        't' => microtime(true), 'r' => $data, 'm' => $model_instance
                    ];
                    $cache->set($cache_key, json_encode($res, JSON_UNESCAPED_UNICODE), $expire);

                } while ($c--);

                $Lock_manager->release($lock);
                if ($res['m']) {
                    $model = new $res['m']();
                    return self::assignModelData($model,$res['r']);
                }
                return $res['r'];
            }
        }
        $res = json_decode($cache->get($cache_key), true);
        if ($res['m']) {
            $model = new $res['m']();
            return self::assignModelData($model,$res['r']);
        }
        return $res['r'] ?? null;
    }

    /**
     * @param $cache_key
     * @return mixed
     */
    private static function wrapDelCache($cache_key)
    {
        return Di('redis')->del($cache_key);
    }

    /**
     * @param \Phalcon\Mvc\Model $model
     * @param array $data
     * @return \Phalcon\Mvc\Model|static
     */
    private static function assignModelData($model,$data)
    {
        /*
        $model->assign($data);
        if ($diff_keys = array_diff_key($model->toArray(),$data)) {
            $metadata = $model->getModelsMetaData();
            $default_values = $metadata->getDefaultValues($model) ?: [];

            $res = array_intersect_key($default_values,$diff_keys);
            foreach ($res as $key => $val) {
                if (is_numeric($val) || $val === ""){
                    $model->$key = $val;
                    //$data[$key] = $val;
                }
            }
        }
        return $model;
        */

        $model->assign($data);
        return \Phalcon\Mvc\Model::cloneResultMap($model,$model->toArray(),null,0,true);

    }

    /**
     * 获取model关联的表主键，如果表是自定义的ID子类需要重写该方法，返回 自定义ID 字段名称
     * @return string
     */
    public function getPrimaryKey()
    {
        $metadata = $this->getModelsMetaData();
        $pks = $metadata->getPrimaryKeyAttributes($this);
        if (empty($pks)) {
            $pks = ['id'];
        }
        return $pks[0];
    }

    /**
     * 获取缓存时间，返回数组，[数据存在缓存时间，数据不存在时缓存时间] 单位：秒
     *
     * @return array
     */
    public function getCacheExpire()
    {
        return [28800,300];
    }

    /**
     * 生成缓存key , 格式：数据库名称.表名称:id值
     *
     * @param $svc_name
     * @param $primary_id
     * @return string
     */
    public function generateCacheKey($svc_name,$primary_id)
    {
        $db_group = $svc_name != 'db' ?  $svc_name : 'default';
        $db_name = Di('config')->path('db.' . $db_group . ".dbname");

        return sprintf("%s.%s:%s",$db_name,$this->getSource(),$primary_id);
    }

    /**
     * 通过主键ID从缓存中获取数据
     *
     * @param $id
     * @return bool|static
     */
    public static function getEntityFromCache($id)
    {
        $model = new static();

        $svc_name = $model->getReadConnectionService();
        $cache_key = $model->generateCacheKey($svc_name,$id);

        /* @var \Redis $redis */
        $redis = Di('redis',false);
        $opt_serializer = $redis->getOption(\Redis::OPT_SERIALIZER);

        if (function_exists('igbinary_serialize')) {
            $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_IGBINARY);
        } else {
            $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
        }

        $data = $redis->hGetAll($cache_key) ?? [];
        if (!$data) {
            $primary_key = $model->getPrimaryKey();
            $res = static::findFirst([
                'conditions' => "{$primary_key} = :obj_id:",
                'bind' => ['obj_id' => $id]
            ]);

            $expires = $model->getCacheExpire();
            $data = [$cache_key => 1];
            $exp_time = $expires[1] ?? ($expires[0] ?? 60);

            if ($res) {
                $data = $res->toArray();
                $exp_time = $expires[0] ?? 300;
            }

            $redis->hMSet($cache_key,$data);
            $redis->expire($cache_key,$exp_time);
        }
        $redis->setOption(\Redis::OPT_SERIALIZER, $opt_serializer);
        unset($data[$cache_key]);
        if ($data) {
            $model = self::assignModelData($model,$data);
            return $model;
        }
        return false;
    }

    /**
     * 更新或新增 缓存数据
     *
     * @param string $event_name
     * @param null|integer|string $id
     * @return bool
     */
    public function upsetEntityCache($event_name,$id = null)
    {
        if ($id === null) {
            $primary_key = $this->getPrimaryKey();
            $id = $this->$primary_key;
        }
        $svc_name = $this->getReadConnectionService();
        $cache_key = $this->generateCacheKey($svc_name,$id);

        /* @var \Redis $redis */
        $redis = Di('redis',false);
        $ret = $redis->del($cache_key);

        if (method_exists($this,'onUpsetEntityCache')) {
            static::onUpsetEntityCache($this,$event_name);
        }
        return $ret;
    }

    /**
     * afterFetch
     */
    public function afterFetch()
    {
        if (self::$json_serialize_func && method_exists($this,self::$json_serialize_func)) {
            call_user_func([$this,self::$json_serialize_func]);
        }
    }

    /**
     * Inserting event
     *
     * @param null $event_ref
     * @return bool
     */
    public function afterCreate()
    {
        return $this->_autoUpsetModelCache(self::UPSET_ENTITY_EVENT_CREATE);
    }

    /**
     * Updating event
     * @return bool
     */
    public function afterUpdate()
    {
        return $this->_autoUpsetModelCache(self::UPSET_ENTITY_EVENT_UPDATE);
    }

    /**
     * Deleting event
     */
    public function afterDelete()
    {
        return $this->_autoUpsetModelCache(self::UPSET_ENTITY_EVENT_DELETE);
    }

    /**
     * @param null $parameters
     * @return Model\ResultsetInterface|void
     */
    public static function find($parameters = null)
    {
        if (isset($parameters['struct'])) {
            static::setJsonSerializeFunc($parameters['struct']);
        }
        return parent::find($parameters);
    }

    /**
     * @param null $parameters
     * @return static|Model
     */
    public static function findFirst($parameters = null)
    {
        if (isset($parameters['struct'])) {
            static::setJsonSerializeFunc($parameters['struct']);
        }
        return parent::findFirst($parameters);
    }

    /**
     * 自动更新缓存
     *
     * @param $event_func
     * @param null $event_ref
     * @return bool
     */
    public function _autoUpsetModelCache($event_func,$event_ref = null)
    {
        if (!in_array($event_func,$this->enabled_cache_events)) {
            return false;
        }

        if ($event_ref === null) {
            if (!$this->getWriteConnection()->isUnderTransaction()) {
                return $this->upsetEntityCache($event_func);
            }
            self::$delay_after_events[] = [[$this,'_autoUpsetModelCache'],[$event_func]];
            return true;
        }
        if ($event_ref == 'commitTransaction') {
            return $this->upsetEntityCache($event_func);
        }
        return false;
    }

    /**
     * @param bool $reset
     * @return array
     */
    public static function getDelayModelEvents($reset = false)
    {
        $ret = self::$delay_after_events;
        if ($reset) {
            self::$delay_after_events = [];
        }
        return $ret;
    }

    /**
     * 获取更新的字段名称
     * @return array
     */
    public function getChangedFields()
    {
        try {
            $ret = parent::getChangedFields();
        } catch (\Exception $e) {
            $ret = array_keys($this->toArray());
        }
        return $ret;
    }

    /**
     * 设置是否使用缓存
     * @param array $events
     * @return $this
     */
    public function setEnableCacheEvents(array $events)
    {
        $this->enabled_cache_events = $events;
        return $this;
    }

    /**
     * @param $func
     */
    public static function setJsonSerializeFunc($func)
    {
        $prefix = 'jsonSerialize';
        if (substr($func,0,strlen($prefix)) == $prefix) {
            self::$json_serialize_func = $func;
        }else {
            self::$json_serialize_func = $prefix . ucfirst($func);
        }
    }

    /**
     * @return array|ModelBase
     */
    public function jsonSerialize()
    {
        if (!$this->json_serialize &&  method_exists($this,'jsonSerializeDefault')) {
            $this->jsonSerializeDefault();
        }
        return $this->json_serialize ?: $this;
    }

    /**
     * 批量添加
     *
     * @param array $data 二维数组
     * @param bool $replace
     * @param bool $ignore
     * @param string $tableName
     * @param string $duplicate_update field_name = VALUES(field_name)
     * @return mixed
     * @throws \Exception
     */
    public function saveAll($data, $replace = false, $ignore = false, $tableName = '', $duplicate_update = '')
    {
        if (empty($tableName)) {
            $tableName = $this->getSource();
        }
        if ($replace) {
            $sql = "REPLACE";
        } else {
            $sql = !$ignore ? "INSERT" : "INSERT IGNORE";
        }

        $update = '';
        if ($sql == 'INSERT' && $duplicate_update != '') {
            $update = sprintf(" ON DUPLICATE KEY UPDATE %s", $duplicate_update);
        }

        if (count($data) == count($data,1)) {
            throw new \Exception("参数必须是二维数组!");
        }
        $data = array_values($data);

        $cols = implode(',', array_keys($data[0]));
        $vals = implode(',', array_fill(0, count($data[0]), '?'));
        $vals = ltrim(str_repeat(",({$vals})", count($data)), ',');
        $sql .= " INTO {$tableName} ({$cols}) VALUES {$vals} {$update}";

        /* @var \PDOStatement $sth */
        $sth = $this->getWriteConnection()->prepare($sql);
        $i = 1;
        foreach ($data as $line => $row) {
            foreach ($row as $k => &$v) {
                $sth->bindParam($i, $v);
                $i++;
            }
        }

        if (!$sth->execute()){
            return false;
        }
        return $sth->rowCount();
    }

    /**
     * 按照条件更新数据
     *
     * @param $data
     * @param $where
     * @return mixed
     * @throws \Exception
     */
    public function execUpdate($data, $where)
    {
        if (empty($where['conditions'])) {
            throw new \Exception("缺少参数 conditions");
        }

        if (preg_match_all("/(:[\w]+):/", $where['conditions'], $match)) {
            $where['conditions'] = str_replace($match[0], $match[1], $where['conditions']);
        }
        $conditions = $where['conditions'];
        $bind = isset($where['bind']) ? $where['bind'] : [];

        $fields = [];
        foreach ($data as $col => $val) {
            $fields[] = "`{$col}` = :{$col}";
        }
        $sql = sprintf("UPDATE `%s` SET %s WHERE %s", $this->getSource(), implode(',', $fields), $conditions);
        /* @var \PDOStatement $sth */
        $sth = $this->getWriteConnection()->prepare($sql);

        foreach (array_merge($data, $bind) as $k => $v) {
            $sth->bindValue(":" . trim($k, ":"), $v);
        }
        if (!$sth->execute()){
            return false;
        }
        return $sth->rowCount();
    }

    /**
     * 按照条件删除数据（物理删除）
     *
     * @param $where
     * @param int $limit 删除条数，默认1，0 为不限制
     * @return mixed
     * @throws \Exception
     */
    public function execDelete($where, $limit = 1)
    {
        if (empty($where['conditions'])) {
            throw new \Exception("缺少参数 conditions");
        }

        if (preg_match_all("/(:[\w]+):/", $where['conditions'], $match)) {
            $where['conditions'] = str_replace($match[0], $match[1], $where['conditions']);
        }
        $conditions = $where['conditions'];
        $bind = isset($where['bind']) ? $where['bind'] : [];

        $sql = sprintf("DELETE FROM `%s` WHERE %s", $this->getSource(), $conditions);
        if ($limit) {
            $sql .= " LIMIT " . intval($limit);
        }

        /* @var \PDOStatement $sth */
        $sth = $this->getWriteConnection()->prepare($sql);

        foreach ($bind as $k => $v) {
            $sth->bindValue(":" . trim($k, ":"), $v);
        }

        if (!$sth->execute()){
            return false;
        }
        return $sth->rowCount();
    }
}
