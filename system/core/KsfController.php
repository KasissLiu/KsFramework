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

    private $reponseHeaders = array();

    private $ksfInstance;

    public function __construct()
    {
        $this->ksfInstance = Ksf::getInstance();
        if (method_exists($this, 'init')) {
            $this->init();
        }
    }

    public function __get($prop)
    {
        return $this->ksfInstance->$prop ? $this->ksfInstance->$prop : null;
    }

    public function getView()
    {
        return $this->ksfInstance->render;
    }

    public function getError()
    {
        return $this->ksfInstance->error;
    }

    public function getRequest()
    {
        return $this->ksfInstance->input;
    }
    
    public function responseJson($error, $msg, $data = array(), $code = 0, $status = 200)
    {
        $this->setHeaders('Content-Type', 'application/json')
             ->setHeaders('Status', $status);
        $json = array(
            'err' => $error,
            'msg' => $msg,
            'data' => $data,
            'code' => $code
        );
        $this->doResponse(json_encode($json));
    }
    
    public function responseRaw($content,$status)
    {
        $this->setHeaders('Status', $status);
        $msg = is_array($content) ? json_encode($content) : $content;
        $this->doResponse($msg);
    }
    
    protected function setHeaders($key,$value)
    {
        $this->reponseHeaders = array_merge($this->reponseHeaders,array($key=>$value));
        return $this;
    }
    private  function doResponse($data)
    {
        foreach($this->reponseHeaders as $key=>$value)
        {
            header($key.': '.$value);
        }
        echo $data;
        die();
    }
}