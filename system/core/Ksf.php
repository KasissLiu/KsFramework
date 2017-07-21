<?php
/**
 * Created by PhpStorm.
 * User: kasiss
 * Date: 5/24/16
 * Time: 23:31
 */




class Ksf
{
    private $app_name;  //App Name

    private $router;    //App Router
    private $input;     //App Input
    private $render;    //App Render
    private $exception; //App Exception
    private $error;     //App Error Msg

    private $actController; //Request Controller
    private $actAction;     //Request Action


    private $dispatcher;    //Request Dispatcher

    private static $_instance;  //To Save an Instance of Ksf


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
    /**
     * if file application/bootstrap.php exists 
     *  to excute the methods whose name begins with '_init'  
     */
    public function Bootstrap()
    {
        try {
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
    //         $this->exception = new KsfException();
            return $this;
        } catch ( KsfException $e ) {
            die( $e->getMessage() );   
        } 
    }

    /**
     * 向实例内添加自定义属性
     * @param unknown $prop
     * @param unknown $val
     */
    public function set($prop,$val)
    {

        $this->$prop = $val;

        return true;
    }

    /**
     * 获取实例内的属性
     * @param unknown $prop
     */
    public function __get($prop)
    {
        return isset($this->$prop) ? $this->$prop : null;
    }
    /**
     * web请求时处理请求
     */
    public function run()
    {
        try {
            $this->preLoad();

            $actController = $this->actController;
            $actAction = $this->actAction;

            $run = new $actController();
            $run->$actAction();
            
        }catch( KsfException $e)
        {
            $this->error = $e;
            $this->error->transToError($this->router);
        }catch(Exception $e)
        {
            throw new KsfException($e->getMessage(),$e->getCode(),$e);
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
            throw  new KsfException("There is no {$controller}Controller!");

        $this->actController = $controller.'Controller';
        $this->actAction = $action.'Action';

    }


    public function execute($object,$method=null,$param=null)
    {  
        try {
            
            if(!class_exists($object))
                throw new KsfException('No Class {'.$object.'} Found!');
 
             $exec_obj = new $object();

            if(!$method)
            {
                $method = 'run';
            }
            if(!method_exists($exec_obj,$method)) {
                    throw new KsfException('No Method {'.$method.'} Found!');
            }
                    $exec_obj->$method($param);
        }catch( KsfException $e)
        {
            $this->error = $e;
            $e->executeError($object);
        }
        catch( Exception $e)
        {
           echo "Please Use KsfException !";
        }
    }



    /**
     * 单例工厂
     * @return Ksf
     */
    public static function getInstance()
    {
        if(!(self::$_instance instanceof self))
            throw new KsfException("The instance has lost!");

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
