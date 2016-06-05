<?php
/**
 * Created by PhpStorm.
 * User: kasiss
 * Date: 5/29/16
 * Time: 21:26
 *
 * 启动类
 * 将在Ksf->bootstrap()时被调用
 * 类内注册的所有 _initXxxx方法将被调用
 */



class Bootstrap
{

    public function _initRouter()
    {
        $Ksf = Ksf::getInstance();
        $KsfConfig = KsfConfig::getInstance();

        $dispatcher = Ksf::get('dispatcher');

        $router = new KsfRouter($dispatcher);

        $router->module = $router->module ?  $router->module : $KsfConfig->get("defaultModule");
        $router->controller = $router->controller ? $router->controller : $KsfConfig->get("defaultController");
        $router->action = $router->action ? $router->action : $KsfConfig->get("defaultAction");
        $Ksf->set('router',$router);
    }


}