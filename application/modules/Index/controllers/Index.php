<?php
/**
 * 默认控制器
 * 如果存在init方法 则init方法会最先执行
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
        echo '<pre>';
        $ksf = Ksf::getInstance();
          var_dump($ksf->router);
          var_dump($ksf->input->get);
          var_dump($_GET);
          
    }
}