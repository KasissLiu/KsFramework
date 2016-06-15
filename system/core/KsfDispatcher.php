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
    private $get = null;
    private $post = null;
    private $files = null;


    public function __construct()
    {
        if(php_sapi_name() !== 'cli') {
            $uri = parse_url($_SERVER['REQUEST_URI']);
            $this->path = isset($uri['path']) ? $uri['path'] : '';
            $this->query = isset($uri['query']) ? $uri['query'] : '';
            $this->post = isset($_POST) ? $_POST : null;
            $this->files = isset($_FILES) ? $_FILES : null;
            $this->get = isset($_GET) ? $_GET : null;
        }
        if(php_sapi_name() === 'cli'){

        }
    }

    public function __get($prop)
    {
        return $this->$prop;
    }

    /**
     * @param $render
     * @return int
     */
    public function setRender($render)
    {
        $this->render = $render;
        return 1;
    }
}