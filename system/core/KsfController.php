<?php
/**
 * Created by PhpStorm.
 * User: kasiss
 * Date: 5/24/16
 * Time: 23:07
 */


class KsfController
{

    public $view = null;
    public $router = null;


    public function __construct()
    {
        if(method_exists($this,'init'))
        {
            $this->init();
        }
    }



}