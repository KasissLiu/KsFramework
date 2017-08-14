<?php

/**
 * User: kasiss
 * Date: 5/24/16
 * Time: 23:49
 * 处理路由参数 解析请求参数
 */
class KsfRouter
{

    private $module;

    private $controller;

    private $action;

    private $params;

    private $querys;

    const DEFAULT_MODE = 1;

    const ORIGINAL_MODE = 2;

    const REWRITE_MODE = 3;

    public function __construct(KsfDispatcher $dispatcher, $uri_mode)
    {
        $scripts = $this->getRouter($dispatcher, $uri_mode);
        $this->querys = $this->getQuerys();
        
        $this->module = isset($scripts[0]) ? $scripts[0] : KsfConfig::getInstance()->get('defaultModule');
        $this->controller = isset($scripts[1]) ? $scripts[1] : KsfConfig::getInstance()->get('defaultController');
        $this->action = isset($scripts[2]) ? $scripts[2] : "";
        KsfConfig::getInstance()->get('defaultAction');
        
        return $this;
    }

    public function getRouter(KsfDispatcher $dispatcher, $uri_mode)
    {
        switch ($uri_mode) {
            case self::DEFAULT_MODE:
                return $this->default_mode($dispatcher);
            case self::ORIGINAL_MODE:
                return $this->original_mode($dispatcher);
            case self::REWRITE_MODE:
                return $this->rewrite_mode($dispatcher);
            default:
                return array();
        }
    }

    public function __get($prop)
    {
        return $this->$prop;
    }

    public function __set($prop, $val)
    {
        return $this->$prop = $val;
    }

    private function default_mode(KsfDispatcher $dispatcher)
    {
        $path = trim($dispatcher->path, '/');
        $this->querys = $dispatcher->query;
        $scripts = explode('/', $path);
        
        return $this->rewrite_check($scripts);
    }

    private function original_mode(KsfDispatcher $dispatcher)
    {
        $query = trim($dispatcher->query, '/');
        
        $querys = explode('&', $query);
        
        if (isset($querys[0])) {
            $route = explode('=', array_shift($querys));
            if ($route[0] == 'r') {
                $this->querys = $querys;
                $scripts = isset($route[1]) ? explode('/', trim($route[1], '/')) : array();
            } else {
                $scripts = array();
            }
        } else {
            $scripts = array();
        }
        return $this->rewrite_check($scripts);
    }

    private function rewrite_mode(KsfDispatcher $dispatcher)
    {
        $path = trim($dispatcher->path, '/');
        $params = explode('/', $path);
        $scripts = array_splice($params, 0, 3);
        for ($i = 0; $i < count($params); $i = $i + 2) {
            $this->params[$params[$i]] = $params[$i + 1];
        }
        $this->querys = $dispatcher->query;
        return $this->rewrite_check($scripts);
    }

    private function rewrite_check($scripts)
    {
        if (! is_array($scripts))
            return null;
        
        foreach ($scripts as $key => $val) {
            if ($val == null)
                unset($scripts[$key]);
        }
        return $scripts;
    }

    private function getQuerys()
    {
        $tmp = array();
        
        $querys = is_string($this->querys) ? explode('&', trim($this->querys, '&')) : $this->querys;
        if (is_array($querys)) {
            foreach ($querys as $val) {
                $param = explode('=', $val);
                if (count($param) > 1) {
                    $tmp[$param[0]] = $param[1];
                }
            }
        }
        return $tmp;
    }
}