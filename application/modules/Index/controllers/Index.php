<?php

/**
 * 默认控制器
 * 如果存在init方法 则init方法会最先执行
 */

use Kasiss\Tools\Area;

class IndexController extends KsfController
{

    public function init()
    {}

    public function indexAction()
    {
        $area = new Area();
        var_dump($area->getProvinces());
       
    }

    public function testAction()
    {
        // do something
        $view = $this->getView();
        $view->display('test.html');
    }
}