<?php

/**
 * Created by PhpStorm.
 * User: kasiss
 * Date: 5/24/16
 * Time: 23:51
 */
class KsfInput
{

    private $get = null;

    private $post = null;
    
    private $rawData = null;
    
    private $headers = null;

    const DEFAULT_MODE = 1;

    const ORIGINAL_MODE = 2;

    const REWRITE_MODE = 3;

    public function __construct(KsfDispatcher $dispatcher, $uri_mode = 1, $validation = 1)
    {
        $this->get = $dispatcher->get;
        $this->post = $dispatcher->post;
        $this->rawData = $dispatcher->rawData;
        $this->headers = $dispatcher->headers;
        
        switch ($uri_mode) {
            case self::DEFAULT_MODE:
                $this->default_mode($dispatcher);
                break;
            case self::ORIGINAL_MODE:
                $this->original_mode($dispatcher);
                break;
            case self::REWRITE_MODE:
                $this->rewrite_mode($dispatcher);
                break;
            default:
        }
        if ($validation)
            $this->filter();
        
        return $this;
    }

    public function filter()
    {
        if (is_array($this->get) && $this->get) {
            foreach ($this->get as &$val) {
                $val = addslashes($val);
            }
        }
        
        if (is_array($this->post) && $this->post) {
            foreach ($this->post as &$val) {
                $val = addslashes($val);
            }
        }
    }

    private function default_mode(KsfDispatcher $dispatcher)
    {
        $path = trim($dispatcher->path, '/');
        if (isset($this->get[$path]))
            unset($this->get[$path]);
    }

    private function original_mode(KsfDispatcher $dispatcher)
    {
        if (isset($this->get['r']))
            unset($this->get['r']);
    }

    private function rewrite_mode(KsfDispatcher $dispatcher)
    {
        $path = trim($dispatcher->path, '/');
        if (isset($this->get[$path]))
            unset($this->get[$path]);
    }

    public function __get($attr)
    {
        return isset($this->$attr) ? $this->$attr : array();
    }
}