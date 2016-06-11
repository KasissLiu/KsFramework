<?php
/**
 * Created by PhpStorm.
 * User: kasiss
 * Date: 6/11/16
 * Time: 22:17
 */


class KsfException
{

    public function __construct()
    {
        return $this;
    }

    /**
     * @param $router
     * @param Exception $e
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
}