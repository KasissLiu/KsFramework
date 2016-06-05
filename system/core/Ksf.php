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


    private $actController;
    private $actAction;


    private $input;

    private static $dispatcher;

    private static $_instance;


    /**
     * Ksf constructor.
     * @param  KsfDispatcher $dispatcher
     */
    public function __construct(KsfDispatcher $dispatcher)
    {
        self::$dispatcher = $dispatcher;
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


        $this->app_name = !$this->app_name ? 'KsFramework' : $this->app_name;
        $this->router = !$this->router ? new KsfRouter(self::$dispatcher) : $this->router;
        $this->input = !$this->input ? new KsfInput() : $this->input;

        return $this;
    }


    public function set($prop,$val)
    {

        $this->$prop = $val;

        return true;
    }


    public function run()
    {

        $this->preLoad();

        $actController = $this->actController;
        $actAction = $this->actAction;


        $run = new $actController();
        $run->$actAction();
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
            throw  new Exception("Class File is Not Found!");

        $this->actController = $controller.'Controller';
        $this->actAction = $action.'Action';

    }




    /**
     * 单例工厂
     * @return Ksf
     */
    public static function getInstance()
    {
        if(!(self::$_instance instanceof self))
            self::$_instance = new self(self::$dispatcher);
        return self::$_instance;
    }

    public static function get($prop)
    {
        return self::$$prop ? self::$$prop : null;
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
