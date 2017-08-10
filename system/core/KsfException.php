<?php

/**
 * User: kasiss
 * Date: 6/11/16
 * Time: 22:17
 */
class KsfException extends Exception
{

    const SYS_ERROR = 500;

    const CONFIG_ERROR = 501;

    protected $user_error_data = null;

    protected $user_error_msg = null;

    public function __construct($msg = "", $code = 0, $data = "")
    {
        ! $code && $code = self::SYS_ERROR;
        parent::__construct($msg, $code);
        $this->user_error_msg = $msg;
        $this->user_error_data = $data;
        return $this;
    }

    /**
     *
     * @param KsfRouter $router            
     */
    public function transToError($router)
    {
        if (file_exists(APP_PATH . "modules/" . $router->module . "/controllers/Error.php")) {
            include_once APP_PATH . "modules/" . $router->module . "/controllers/Error.php";
            call_user_func(array(
                new ErrorController(),
                'ErrorAction'
            ));
        } else 
            if (! is_dir(APP_PATH . "modules/" . $router->module)) {
                header('Location: /');
            } else {
                header('Location: /');
            }
    }

    public function executeError($instance)
    {
        
        // do something when error exception occured in console
        if (method_exists($instance, 'error')) {
            call_user_func(array(
                new $instance(),
                'error'
            ));
        } else {
            echo "There is no $instance Class!";
        }
    }

    public function getErrorMessage()
    {
        return $this->user_error_msg;
    }

    public function getErrorData()
    {
        return $this->user_error_data;
    }
}