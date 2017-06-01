<?php
/**
 * User: kasiss
 * Date: 6/11/16
 * Time: 22:17
 */


class KsfException extends Exception
{
    public $user_error_data = null;
    public $user_error_msg = null;

    public function __construct($msg="",$data="")
    {
        $this->user_error_msg = $msg;
        $this->user_error_data = $data;
        return $this;
    }

    /**
     * @param $router
     */
    public function transToError($router)
    {
        if(file_exists(APP_PATH."modules/".$router->module."/controllers/Error.php")) {
            include_once APP_PATH . "modules/" . $router->module . "/controllers/Error.php";
            call_user_func(array(new ErrorController(),'ErrorAction'));
        }else{
            echo "There is no ErrorController catch Exceptions!";
        }

    }
    
    
    public function executeError($instance)
    {
        
        //do something when error exception occured in console
        if(method_exists($instance, 'error'))
        {
            call_user_func(array(new $instance(),'error'));
        }else{
           echo "There is no $instance Class!";
        }
        
    }
}