<?php
/**
 * Created by PhpStorm.
 * User: kasiss
 * Date: 5/24/16
 * Time: 23:31
 */




class Ksf
{
    private $app_name;

    private $router;
    private $input;
    private $render;
    private $exception;
    private $error;

    private $actController;
    private $actAction;


    private $dispatcher;

    private static $_instance;


    /**
     * Ksf constructor.
     * @param  KsfDispatcher $dispatcher
     */
    public function __construct(KsfDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        if(!(self::$_instance instanceof self))
            self::$_instance = $this;
    }

    public function Bootstrap()
    {

        if(file_exists(APP_PATH.'Bootstrap.php'))
        {
            require_once APP_PATH.'Bootstrap.php';

            $bootstrap = new Bootstrap();
            $methods = get_class_methods($bootstrap);
            foreach($methods as $method)
            {
                if(preg_match('/^_init[A-Z]+[a-z]+/',$method))
                    $bootstrap->$method();
            }
        }

        $ksfConfig = KsfConfig::getInstance();

        if(php_sapi_name() !== 'cli') {
            $this->app_name = !$this->app_name ? 'KsFramework' : $this->app_name;
            $this->router = !$this->router ? new KsfRouter(self::getDispatcher(), $ksfConfig->get('appRouterModule')) : $this->router;
            $this->input = !$this->input ? new KsfInput(self::getDispatcher(), $ksfConfig->get('appRouterModule')) : $this->input;
            //        $this->render = !$this->render ? new KsfRender() : $this->render;  //系统render 未完成
        }
        $this->exception = new KsfException();


        return $this;
    }


    public function set($prop,$val)
    {

        $this->$prop = $val;

        return true;
    }


    public function __get($prop)
    {
        return isset($this->$prop) ? $this->$prop : null;
    }

    public function run()
    {

        $this->preLoad();

        $actController = $this->actController;
        $actAction = $this->actAction;


        $run = new $actController();

        try {
            $run->$actAction();
        }catch(Exception $e)
        {
            $this->error = $e;
            $this->exception->transToError($this->router);
        }
    }

    private function preLoad()
    {
        $module = ucfirst($this->router->module);
        $controller = ucfirst($this->router->controller);
        $action  = $this->router->action;

        $filename = APP_PATH.'modules/'.$module.'/controllers/'.$controller.'.php';
        if(file_exists($filename))
            include_once $filename;
        else
            throw  new Exception("There is no {$controller}Controller!");

        $this->actController = $controller.'Controller';
        $this->actAction = $action.'Action';

    }


    public function execute($object)
    {

        try {
            new $object();
        }catch(Exception $e)
        {
            $this->error = $e;
            $this->exception->transToError($this->router);
        }
    }



    /**
     * 单例工厂
     * @return Ksf
     */
    public static function getInstance()
    {
        if(!(self::$_instance instanceof self))
            throw new Exception("The instance has lost!");

        return self::$_instance;
    }

    public static function get($prop)
    {
        return isset(self::$$prop) ? self::$$prop : null;
    }

    /**
     * 获取保存在Ksf中的dispatcher实例
     * @return KsfDispatcher
     */
    public static function getDispatcher()
    {
        return self::$_instance->dispatcher;
    }
    /**
     * 禁止对象被克隆
     */
    private function __clone()
    {
        // TODO: Implement __clone() method.
        trigger_error('Clone is not allow!',E_USER_ERROR);
    }

}
