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

    private $reponse_headers = array();

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

    public function getRequest()
    {
        return $this->Ksf_instance->input;
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
        $this->reponse_headers = array_merge($this->reponse_headers,array($key=>$value));
        return $this;
    }
    private  function doResponse($data)
    {
        foreach($this->reponse_headers as $key=>$value)
        {
            header($key.': '.$value);
        }
        echo $data;
        die();
    }
}