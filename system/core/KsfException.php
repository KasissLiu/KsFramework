<?php

/**
 * User: kasiss
 * Date: 6/11/16
 * Time: 22:17
 */
class KsfException extends Exception
{

    const SYS_ERROR = 500; //系统错误

    const CONFIG_ERROR = 501; //配置错误

    protected $userErrorData = null;

    protected $userErrorTraces = array();

    protected $userErrorMsg = null;

    protected $userErrorOccured = null;

    public function __construct($msg = "", $code = 0, $data = "")
    {
        ! $code && $code = self::SYS_ERROR;
        parent::__construct($msg, $code);
        $this->userErrorMsg = $msg;
        
        if (is_object($data) && $data instanceof Exception) {
            $this->userErrorTraces = array_merge($this->userErrorTraces, $data->getTrace());
        } else {
            $this->userErrorData = $data;
        }
        
        $this->userErrorOccured = "Line: " . $this->getLine() . " in File " . $this->getFile();
        
        $this->handle();
        
        return $this;
    }

    /**
     * web模式下错误处理
     *
     * @param KsfRouter $router            
     */
    public function transToError($router)
    {
        if (file_exists(APP_PATH . "modules/" . ucfirst($router->module) . "/controllers/Error.php")) {
            include_once APP_PATH . "modules/" . ucfirst($router->module) . "/controllers/Error.php";
            call_user_func(array(
                new ErrorController(),
                'ErrorAction'
            ));
        } else {
            if (! is_dir(APP_PATH . "modules/" . $router->module)) {
                header('Location: /');
            } else {
                header('Location: /');
            }
        }
    }

    /**
     * cli模式下错误处理
     *
     * @param unknown $instance            
     */
    public function executeError($instance)
    {
        // do something when error exception occured in console
        if (method_exists($instance, 'error')) {
            call_user_func(array(
                new $instance(),
                'error'
            ));
        } else {
            echo "There is no $instance Class! \n";
        }
    }

    /**
     * 普通错误 输出
     *
     * @param unknown $e            
     */
    public function dumpError(self $e)
    {
        if (php_sapi_name() !== 'cli') {
            header('Content-Type: text/plain');
        }
        print_r("Message: " . $e->getMessage() . "\n");
        print_r($e->getErrorOccured() . "\n");
        $traces = $e->getErrorTrace();
        print_r("Traces: \n");
        $n = count($traces) - 1;
        while ($n >= 0) {
            print_r("   " . ($n + 1) . ".  Function {$traces[$n]['function']} of Class {$traces[$n]['class']} in {$traces[$n]['file']} on Line {$traces[$n]['line']}\n");
            $n --;
        }
        die();
    }

    /**
     * to run custom error log
     */
    protected function handle()
    {
        $errorHandle = Ksf::getInstance()->errorHandle;
        
        if ($errorHandle) {
            // if param is a function name
            if (is_string($errorHandle) && function_exists($errorHandle)) {
                call_user_func($errorHandle, $this);
                // if param is a method name
            } elseif (is_array($errorHandle) && method_exists($errorHandle[0], $errorHandle[1])) {
                call_user_func($errorHandle, $this);
                // if param is a closure
            } elseif ($errorHandle instanceof Closure) {
                $errorHandle($this);
            }
        } else {
            register_shutdown_function(function ($error) {
                KsfLogger::getInstance()->error($error->getErrorMessage() . " on " . $error->getErrorOccured(), $error->getErrorTrace());
            }, $this);
        }
    }

    /**
     * 获取到报错信息
     */
    public function getErrorMessage()
    {
        return $this->userErrorMsg;
    }

    /**
     * 获取到报错详情
     *
     * @return string
     */
    public function getErrorData()
    {
        return $this->userErrorData;
    }

    /**
     * 获取轨迹
     */
    public function getErrorTrace()
    {
        return array_merge($this->userErrorTraces, $this->getTrace());
    }

    /**
     * 返回错误回溯
     */
    public function getErrorOccured()
    {
        return $this->userErrorOccured;
    }
}