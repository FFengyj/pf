<?php

/**
 * 默认配置
 */

!defined('ROOT_PATH') && define('ROOT_PATH',dirname(__DIR__,3));

return [

    /**
     * 路由配置
     *
     * 格式：
     * -moduleName => route file
     *
     * 默认使用 default 对应的路由文件
     *
     */
    'routes' => [
        'default' => ROOT_PATH . "/config/routes.php"
    ],

    /**
     * 组件配置
     *
     * -path 配置文件目录
     * -adapter 指定解析适配器，支持 'php','ini','json','yaml'
     */
    'components_config' => [
        'path' => ROOT_PATH . "/config/components",
        'adapter' => 'php',
    ],

    /**
     * 文件自动加载配置
     *
     * -set_extensions 扩展白名单，允许加载指定扩展名的文件
     * -register_classes 注册类文件,例：
     *  ['SomeClass'=>'library/OtherComponent/Other/Some.php', 'Example\Base' => 'base.php']
     *
     * -register_files 注册文件，加载 non-classes 文件，例：
     *  ['functions.php' , 'arrayFunctions.php',]
     *
     * -register_dirs 注册目录 [dir1,dir2,...]
     * -register_namespaces 注册命名空间 [ns1,ns2,...]
     *
     */
    'loader' => [
        'set_extensions' => ['php'],
        'register_classes' => [],
        'register_files'   => [],
        'register_dirs'   => [],
        'register_namespaces'   => [],
    ],

    /**
     * 异常配置
     *
     * 格式：
     * 异常类 => 对应的异常处理方法
     *
     * 继承 CustomErrException 的异常类，可不指定handler,使用默认的处理
     * 所有的异常，都可以覆盖配置，使用自己的处理程序
     */
    'exceptions' => [
        Pf\System\Exceptions\UnexpectedException::class => Pf\System\Exceptions\Handler\UnexpectedHandler::class,
        Pf\System\Exceptions\JsonFmtException::class => Pf\System\Exceptions\Handler\JsonFmtHandler::class,
    ],

    /**
     * 数据库配置
     *
     * 单个数据库配置默认使用 default 作为key, 从DI中获取数据库连接，使用  $di->get('db');
     *
     * 如果有多个数据库配置:
     * 'db' => ['default' => [...],'other_db' => [...]]
     *  使用 $di->get('db') 、$di->get('other_db')、分别获取数据库service
     */
    'db' => [
        'default' => [
            'adapter'  => env('DB_DRIVER','mysql'),
            'host'     => env('DB_HOST','127.0.0.1'),
            'port'     => env('DB_PORT',3306),
            'username' => env('DB_USERNAME','root'),
            'password' => env('DB_PASSWORD',''),
            'dbname'   => env('DB_DATABASE',''),
            'charset'  => env('DB_CHARSET','utf8mb4'),

            //pdo mysql options
            'options' => [
                PDO::ATTR_TIMEOUT => 10,
            ]
        ]
    ],

    /**
     * redis 配置
     * 使用 $di->get('redis')，获取 default 配置的Redis 实例
     */
    'redis' => [
        'default' => [
            'host' => env('REDIS_HOST','127.0.0.1'),
            'port' => env('REDIS_PORT',6379),
            'auth' => env('REDIS_AUTH',''),
            'db'   => env('REDIS_DB',0),
            'timeout'  => 3,         //value in seconds (optional, default is 0.0 meaning unlimited)
            'reserved' => null,     //should be null if $retry_interval is specified
            'retry_interval' => 0,  //retry interval in milliseconds.
            'read_timeout' => 0,    //value in seconds (optional, default is 0 meaning unlimited)
            'options' => []         //[key => value]
        ],
    ],

    /**
     * beanstalk 配置
     */
    'beanstalk' => [
        'host' => env('BEANSTALK_HOST','127.0.0.1'),
        'port' => env('BEANSTALK_PORT',11311),
        'connect_timeout' => env('BEANSTALK_CONNECT_TIMEOUT',5),
        'connect_persistent' => env('BEANSTALK_CONNECT_PERSISTENT',false),
    ],

    /**
     * 日志配置
     * -default logger 默认配置，使用 $di->get('logger') 获取
     *  - class 适配器类，[Phalcon\Logger\Adapter\File ，Phalcon\Logger\Adapter\Stream , ...] 或自定义
     *  - path 日志目录，目录不存在将自动创建，日志文件名为 default.log
     *  - level 设置日志级别，小于等于该级别的日志，将会记录. 查看 \Phalcon\Logger 常量定义
     */
    'logger' => [
        'default' =>  [
            'class' => \Pf\System\Core\Logger\Custom::class,
            'params' => [
                'path'  => env('DEFAULT_LOGGER_PATH',ROOT_PATH .'/runtime/std'),
                'level' => env('DEFAULT_LOGGER_LEVEL',7), // default \Phalcon\Logger::DEBUG
            ]
        ],
        'logger.stdout' =>  [
            'class' => Pf\System\Core\Logger\LoggerStdout::class,
            'params' => [
                'filename' => "php://stdout",
                'level' => env('DEFAULT_LOGGER_LEVEL',7), // default \Phalcon\Logger::DEBUG
            ]
        ]
    ],

    /**
     * 模板配置（视图）
     *
     * -adapter 模板引擎适配器
     * -base_dir 模板根目录,例如： ROOT_PATH . '/app/Views/'
     * -engines 模板引擎，通过模板扩展名，分别对应不通的模板解析
     */
    'view' => [
        'adapter' => Phalcon\Mvc\View::class,
        'base_dir' => ROOT_PATH . '/app/Views/',
        'engines' => [
            '.html' => Phalcon\Mvc\View\Engine\Php::class
        ]
    ],

    /**
     * 注册自定义容器服务
     *
     * 通过类名注册
     * [string className,bool isShare]
     *
     * 通过服务提供者注册
     * [ServiceProvider]
     * ServiceProvider must implement interface Phalcon\Di\ServiceProviderInterface
     *
     * 通过匿名函数
     * [function(){return new service();},bool isShare]
     */
    'custom_di' => [

        /**
         * flash
         */
        'flash' => [\Pf\System\Core\Plugin\Flash::class,true],

        /**
         * beanstalk 消息队列服务
         */
        'queue' => [\Pf\System\Core\Provider\ServiceBeanstalk::class],

        /**
         * redis 服务
         */
        'redis' => [\Pf\System\Core\Provider\ServiceRedis::class],

        /**
         * 内存锁
         */
        'lock' => [\Pf\System\Core\Provider\ServiceLock::class],

        /**
         * response
         */
        'response' => [\Pf\System\Core\Plugin\Response::class,true],

        /**
         * request
         */
        'request' => [\Pf\System\Core\Plugin\Request::class,true],
    ],

    /**
     * 内存锁配置
     *
     * - adapter
     * - memory_svc
     */
    'lock' => [
        'adapter'   => \Pf\System\Lock\RedisAdapter::class,
        'memory_svc' => 'redis',
        'options' => [
            'lock_timewait'    => 200,   //单位毫秒，内存锁获取时重试等待时间，默认等待200毫秒
            'lock_timeout'     => 15000, //单位毫秒，锁的过期时间，默认15秒
            'lock_retry_times' => 100,   //重试次数
            'lock_prefix'      => 'L_'   //内存锁的key值前缀
        ]
    ],

    /**
     * 系统事件监听
     *
     * 配置格式：
     *  组件名称 => [
     *    [attach => 事件名称，listener => 监听器, priority=>优先级,不指定使用定义顺序]
     *  ]
     */
    'event' => [

        /**
         * 数据库操作事件
         *
         * attach:
         *  db
         *  db:afterQuery
         *  db:beforeQuery
         *  db:beginTransaction
         *  db:createSavepoint
         *  db:commitTransaction
         *  db:releaseSavepoint
         *  db:rollbackTransaction
         *  db:rollbackSavepoint
         */
        'db' => [
            [
                'attach'   => 'db',
                'listener' => Pf\System\Listener\DbListener::class
            ]
        ],

        /**
         * MVC 分发调度事件
         *
         * attach:
         *  dispatch,
         *  dispatch:afterExecuteRoute
         *  dispatch:afterDispatch
         *  dispatch:afterDispatchLoop
         *  ...
         */
        'dispatcher:mvc' => [],

        /**
         * 命令行应用 分发调度事件
         */
        'dispatcher:cli' => [],

        /**
         * 数据库模型事件
         *
         * attach:
         *  model:afterCreate
         *  model:beforeCreate
         *  model:beforeSave
         *  ...
         */
        'modelsManager' => [],
        'loader' => [],
        'router' => [],
        'application' => [],
        'console' => [],
        'view' => [],
        'collectionManager' => [],
    ]

];
