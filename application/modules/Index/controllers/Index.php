<?php
/**
 * Created by PhpStorm.
 * User: kasiss
 * Date: 5/25/16
 * Time: 00:19
 */


class IndexController extends KsfController
{
    public function init()
    {

    }
    public function indexAction()
    {
       echo "hello world!";
    }

    public function testAction()
    {
        echo "testAction";
    }
}