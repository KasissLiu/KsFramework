<?php

/**
 * User: kasiss
 * Date: 5/24/16
 * Time: 23:07
 */
class KsfController
{

    public $view = null;

    public $router = null;

    private $Ksf_instance;

    public function __construct()
    {
        $this->Ksf_instance = Ksf::getInstance();
        if (method_exists($this, 'init')) {
            $this->init();
        }
    }

    public function __get($prop)
    {
        return $this->Ksf_instance->$prop ? $this->Ksf_instance->$prop : null;
    }

    public function getView()
    {
        return $this->Ksf_instance->render;
    }

    public function getError()
    {
        return $this->Ksf_instance->error;
    }
}