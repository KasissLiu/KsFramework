<?php

/**
 *
 * 启动类
 * 将在Ksf->bootstrap()时被调用
 * 类内注册的所有 _initXxxx方法将被调用
 */
class Bootstrap
{

    /**
     * costom your own router
     * do rewrite or other thing
     * if the router of Ksf is null it will be set by default config
     */
    public function _initRouter()
    {
        
    }

    /**
     * to set a render for Ksf
     * Ksf has no default render
     * set one if you have to render pages
     */
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
        
        // 自定义template_dir
        $router = $Ksf->router;
        
        if ($router && is_dir(APP_PATH . "modules/" . ucfirst($router->module) . "/views/")) {
            $smarty->setTemplateDir(APP_PATH . "modules/" . ucfirst($router->module) . "/views/");
        }
        
        $Ksf->set('render', $smarty);
    }

    /**
     * to init Servers
     * 
     * @throws KsfException
     */
    public function _initServers()
    {
        $Ksf = Ksf::getInstance();
        $servers = KsfConfig::getInstance()->get('servers');
        foreach ($servers as $serverName => $server) {
            if (! $server['init'])
                continue;
            
            $server_type = 'server_' . $server['type'];
            if (class_exists($server_type)) {
                $server_obj = new $server_type($server);
                $Ksf->set($serverName, $server_obj);
            } else {
                throw new KsfException('can not make server ' . $serverName);
            }
        }
    }

    /**
     * to set a handle to record errors
     * the handdle can be a function name, a static method ,
     * an object with method , or a closure
     */
    public function _initErrorHandle()
    {
        $Ksf = Ksf::getInstance();
        $Ksf->set('errorHandle', function ($e) {
            // do something to record errors
        });
    }
}