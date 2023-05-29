<?php

namespace Pf\System;

use Dotenv\Dotenv;
use Dotenv\Repository\Adapter\PutenvAdapter;
use Dotenv\Repository\RepositoryBuilder;
use Pf\System\Core\ExceptionHandler;
use Pf\System\Core\Provider\ServiceDefaults;
use Phalcon\Config;
use Phalcon\Di;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Phalcon\Loader;

/**
 * Class Bootstrap
 * @package Pf\System
 * @author fyj
 */
class Bootstrap
{
    /**
     * @var DiInterface|Di
     */
    private $di;

    /**
     * @var array
     */
    private $options;

    /**
     * Bootstrap constructor.
     * @param DiInterface $di
     * @param array $options
     */
    public function __construct(DiInterface $di,$options = [])
    {
        $this->di = $di;
        $this->options = $this->getDefaultOptions($options);

        $repository = RepositoryBuilder::createWithNoAdapters()
            ->addAdapter(PutenvAdapter::class)
            ->immutable()
            ->make();
        Dotenv::create($repository, [$this->options['root_path']])->safeLoad();

        ini_set('display_errors', env('DISPLAY_ERRORS',false));
    }

    /**
     * @param $class
     * @param mixed $callback
     * @return array
     */
    public function __invoke($class,$callback = null)
    {
        $this->di->setShared('config',$this->initConfig());
        $this->di->setShared('loader',$this->registerLoader());

        $this->di->register(new ServiceDefaults($this->options));
        $this->registerEvents();

        //init exception handler
        $exception_handlers = $this->di->get('config')->exceptions->toArray();
        ExceptionHandler::init($exception_handlers);

        //custom di
        $this->registerCustomService();

        /* @var \Phalcon\Application $app */
        $app = new $class();
        if (!($app instanceof \Phalcon\Application)) {
            trigger_error("{$class} is not instanceof '\Phalcon\Application' ",E_USER_ERROR);
        }
        $app->setDI($this->di);
        $event_key = substr(strrchr($class,'\\'),1);
        if ($em = attach_events($this->di->get('config')->path('event.'.lcfirst($event_key),[]))) {
            $app->setEventsManager($em);
        }

        if ($callback === null) {
            return [$app,$this->di];
        }
        call($callback,[$app,$this->di]);
    }

    /**
     * @return Config
     */
    protected function initConfig()
    {

        $config = require __DIR__ . '/Config/Default.php';
        if (file_exists($this->options['root_path'] . "/config/config.php")) {

            $user_config = require $this->options['root_path'] . "/config/config.php";

            $com_cfg = $user_config['components_config'] ?? [];
            if ($com_cfg) {

                $files = glob($com_cfg['path'] . "/*." . $com_cfg['adapter']) ?: [];
                foreach($files as $path) {
                    $key = str_replace('.'.$com_cfg['adapter'],'',strtolower(basename($path)));
                    $user_config[$key] = Config\Factory::load([
                        'filePath' => $path,
                        'adapter'  => $com_cfg['adapter'],
                    ])->toArray();
                }
            }
            $config = config_merge($config,$user_config);
        }

        return new Config($config);
    }

    /**
     * @param $options
     * @return mixed
     */
    protected function getDefaultOptions($options)
    {
        if (!isset($options['root_path'])) {
            $options['root_path'] = defined('ROOT_PATH') ? ROOT_PATH : getcwd();
        }

        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH',$options['root_path']);
        }

        if (!isset($options['module_name'])) {
            $options['module_name'] = defined('MODULE_NAME') ? MODULE_NAME : '';
        }
        $options['module_name'] = strtolower($options['module_name']);

        return $options;
    }

    /**
     * 自动加载
     * @return Loader
     */
    protected function registerLoader()
    {

        $config = $this->di->get('config')->loader->toArray();
        $loader = new Loader();

        if (!empty($config['set_extensions'])) {
            $loader->setExtensions($config['set_extensions']);
        }

        //register default dirs
        if (empty($config['register_dirs']) && file_exists($this->options['root_path'].'/app')) {
            $loader->registerDirs([$this->options['root_path'].'/app']);
        }

        $register_map = [
            'register_classes' => 'registerClasses',
            'register_files'   => 'registerFiles',
            'register_dirs'    => 'registerDirs',
            'register_namespaces' => 'registerNamespaces'
        ];

        foreach ($register_map as $key => $func) {

            if (!empty($config[$key])) {
                $loader->$func($config[$key]);
            }
        }

        if ($em = attach_events($this->di->get('config')->path('event.loader',[]))) {
            $loader->setEventsManager($em);
        }

        return $loader->register();
    }

    /**
     * 注册自定义DI服务
     * @return void
     */
    protected function registerCustomService()
    {
        $config = $this->di->get('config')->path('custom_di',[]);

        foreach ($config as $name => $provider) {

            if ($provider === null) continue;

            if (is_string($provider[0]) && class_exists($provider[0])) {

                $implements = class_implements($provider[0]);
                if (isset($implements[ServiceProviderInterface::class])) {
                    $this->di->register(new $provider[0]($name));
                } else {
                    $this->di->set($name,$provider[0],$provider[1] ?? true);
                }
            } elseif ($provider[0] instanceof \Closure || is_object($provider[0])) {
                $this->di->set($name,$provider[0],$provider[1] ?? true);
            }
        }
    }


    /**
     * 注册事件
     * @return void
     */
    protected function registerEvents()
    {
        $listeners = $this->di->get('config')->event;

        //调度器分发事件
        $dispatcher = $listeners['dispatcher:mvc'] ?? [];
        $dispatcher_svc = \Phalcon\Mvc\Dispatcher::class;
        if (php_sapi_name() == 'cli') {
            $dispatcher_svc = \Phalcon\Cli\Dispatcher::class;
            $dispatcher = $listeners['dispatcher:cli'] ?? [];
        }
        //通过model获取调度事件
        $model_name = $this->options['module_name'] ?: "default";
        $listeners['dispatcher'] =  $dispatcher[$model_name] ?? [];

        $sys_service = [
            'dispatcher'    => $dispatcher_svc,
            'modelsManager' => \Phalcon\Mvc\Model\Manager::class,
            'collectionManager' => \Phalcon\Mvc\Collection\Manager::class,
        ];
        foreach ($sys_service as $key => $class) {

            if ($listeners[$key] && $this->di->has($key)) {
                $service = $this->di->getService($key)->isResolved();
                if (!$service) {
                    $em = attach_events($listeners[$key]);
                    $this->di->setShared($key,function() use($class,$em) {
                        $obj = new $class();
                        if ($em && $obj instanceof \Phalcon\Events\EventsAwareInterface) {
                            $obj->setEventsManager($em);
                        }
                        return $obj;
                    });
                }
            }
        }
    }





}


