<?php
/**
 * Created by PhpStorm.
 * User: kasiss
 * Date: 6/12/16
 * Time: 23:48
 */

class console extends KsfConsole
{
    public function init()
    {

    }
    public function error()
    {
        $error = Ksf::getInstance()->error;
        print_r($error);
    }
    public function run($params='')
    {
        var_dump($params);
        
    }
    public function test($param) {
        var_dump($param);
    }
}