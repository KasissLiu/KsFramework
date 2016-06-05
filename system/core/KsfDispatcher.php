<?php
/**
 * Created by PhpStorm.
 * User: kasiss
 * Date: 5/30/16
 * Time: 09:59
 */



class KsfDispatcher
{

    private $path = null;
    private $query = null;

    public function __construct()
    {
        $uri = parse_url($_SERVER['REQUEST_URI']);
        $this->path = isset($uri['path']) ? $uri['path'] : '' ;
        $this->query = isset($uri['query']) ? $uri['query'] : '' ;
    }

    public function __get($prop)
    {
        return $this->$prop;
    }

}