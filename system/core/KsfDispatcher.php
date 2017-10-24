<?php

/**
 * User: kasiss
 * Date: 5/30/16
 * Time: 09:59
 */
class KsfDispatcher
{

    private $path = null;

    private $query = null;

    private $get = null;

    private $post = null;

    private $files = null;
    
    private $rawData = null;
    
    private $headers = null;
    
    private $uri = null;

    /**
     * to dispatch request and store datas
     */
    public function __construct()
    {
        if (php_sapi_name() !== 'cli') {
            $uri = parse_url($_SERVER['REQUEST_URI']);
            $this->uri = $_SERVER['REQUEST_URI'];
            $this->path = isset($uri['path']) ? $uri['path'] : '';
            $this->query = isset($uri['query']) ? $uri['query'] : '';
            $this->post = isset($_POST) ? $_POST : null;
            $this->files = isset($_FILES) ? $_FILES : null;
            $this->get = isset($_GET) ? $_GET : null;
            $this->rawData = file_get_contents("php://input");
            $this->headers = $this->getHeaders();
        }
        if (php_sapi_name() === 'cli') {}
    }

    public function __get($prop)
    {
        return $this->$prop;
    }
    
    private function getHeaders()
    {
        $headers = array();
        foreach($_SERVER as $key => $value) {
            if(substr($key, 0, 5) === 'HTTP_') {
                $key = substr($key, 5);
                $key = strtolower($key);
                $key = str_replace('_', ' ', $key);
                $key = ucwords($key);
                $key = str_replace(' ', '-', $key);
        
                $headers[$key] = $value;
            }
        }
        return $headers;
    }
    

}