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

        $dispatcher = Ksf::getDispatcher();

        $router = new KsfRouter($dispatcher);

        $router->module = $router->module ?  $router->module : $KsfConfig->get("defaultModule");
        $router->controller = $router->controller ? $router->controller : $KsfConfig->get("defaultController");
        $router->action = $router->action ? $router->action : $KsfConfig->get("defaultAction");
        $Ksf->set('router',$router);
    }


    public function _initSmarty()
    {
        $Ksf = Ksf::getInstance();
        $smarty_config = KsfConfig::getInstance()->get("smarty");

        $smarty = new Smarty();
        $smarty->setLeftDelimiter($smarty_config["left_delimiter"]);
        $smarty->setRightDelimiter($smarty_config['right_delimiter']);
        $smarty->setTemplateDir($smarty_config["template_dir"]);
        $smarty->setCompileDir($smarty_config["compiles_root"]);
        $smarty->setCacheDir($smarty_config["cache_root"]);


        //自定义template_dir
        $router = $Ksf->router;;

        if(is_dir(APP_PATH."modules/".strtolower($router->module)."/views/"))
            $smarty->setTemplateDir(APP_PATH."modules/".strtolower($router->module)."/views/");


        $Ksf->set('render',$smarty);
    }
    
    public function _initServers()
    {
        $Ksf = Ksf::getInstance();
        $servers = KsfConfig::getInstance()->get('servers');
        foreach($servers as $server_name => $server)
        {
            if(!$server['init'])
                continue;

            $server_type = 'server_'.$server['type'];
            try{
                if(class_exists($server_type))
                {
                    $server_obj = new $server_type($server);
                    $Ksf->set($server_name, $server_obj);
                }else{ 
                    throw new KsfException('can not make server '.$server_name);
                }
            }catch( KsfException $e){
                print_r($e->getMessage());
            }
        }

    }


}