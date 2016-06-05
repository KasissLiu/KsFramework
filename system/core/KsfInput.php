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

    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;

        $this->filter();

        return $this;
    }

    public function filter()
    {
        if(is_array($this->get) && $this->get)
        {
            foreach($this->get as &$val)
            {
                $val = addslashes($val);
            }
        }

        if(is_array($this->post) && $this->post)
        {
            foreach($this->post as &$val)
            {
                $val = addslashes($val);
            }
        }
    }



}