<?php
/**
 * Created by PhpStorm.
 * User: kasiss
 * Date: 5/25/16
 * Time: 23:30
 *
 * Ksf 配置类
 * 主要用于储存用户设置的配置内容
 * 便于其他类调用
 */


class KsfConfig
{
    private $appName = "";                      //应用名称
    private $appLibraryPath = "";               //应用类库
    private $appCachePath = "";                 //应用缓存地址
    private $appDebug = false;                  //应用调试模式

    private $autoloadLibrary = array();         //应用需要自动加载的类文件
    private $appModules = array();              //应用可用模块

    private $appRouterModule = 1;               //应用路由模式

    private $servers = array();                 //应用所使用的链接配置

    private static $_instance;                  //存储单例

    /**
     * KsfConfig constructor.
     * 初始化appconfig 文件
     * 初始化serverconfig 文件
     */
    public function __construct()
    {
        $this->_initAppConfig();
        $this->_initServerConfig();

    }

    /**
     * 加载app配置文件
     * 加载失败后 抛出异常
     * @throws Exception
     */
    private function _initAppConfig()
    {
        if(file_exists(ROOT_PATH."conf/appConfig.php"))
            $app_config = require_once(ROOT_PATH."conf/appConfig.php");
        else
            throw  new Exception("appConfig File Not Found!");

        if(is_array($app_config))
        {
            foreach($app_config as $prop => $value)
            {
                $this->set($prop,$value);
            }
        }
    }

    /**
     * 加载server配置文件
     * 加载失败时 抛出异常
     * @throws Exception
     */
    private function _initServerConfig()
    {
        if(file_exists(ROOT_PATH."conf/serverConfig.php"))
            $server_config = require_once(ROOT_PATH."conf/serverConfig.php");
        else
            throw  new Exception("dbConfig File Not Found!");

        if(is_array($server_config))
        {
            foreach($server_config as $value)
            {
                $server = array();
                $server = $value;
                unset($server['server']);
                $this->servers[$value['server']] = $server;
            }
        }
    }


    /**
     * 用户可自定义添加配置
     * @param $prop
     * @param $value
     */
    public function set($prop,$value)
    {
        $this->$prop = $value;
    }

    /**
     * 用户通过属性名 获取配置内容
     * @param $prop
     * @return null
     */
    public function get($prop)
    {
        return ($this->$prop) ? $this->$prop : null;
    }

    /**
     * 用户根据配置文件中的server名称
     * 取相应配置
     * @param $server_name
     * @return null
     */
    public function getServerConfig($server_name)
    {
        return isset($this->servers[$server_name]) ? $this->servers[$server_name] : null;
    }

    /**
     * 单例工厂
     * 返回配置实例本身
     * @return KsfConfig
     */
    public static function getInstance()
    {
        if(!(self::$_instance instanceof self))
            self::$_instance = new self;
        return self::$_instance;
    }
    /**
     *
     * 自定义载入配置文件
     */
    public static function import($config_file,$prop_name=null)
    {
        $instance = self::getInstance();
        
        if(file_exists(ROOT_PATH."conf/".$config_file))
            $user_config = require_once(ROOT_PATH."conf/".$config_file);
        else
            throw  new Exception("Config File Not Found!");
    
        if(is_array($user_config))
        {
            foreach($user_config as $prop => $value)
            {
                if($prop_name)
                {
                    if($prop_name == $prop)
                    {
                        $instance->set($prop,$value);
                        break;
                    }
                }else{
                    $instance->set($prop, $value);
                }
            }
        }
        
        return $prop_name ? $instance->get($prop_name) : true;
        
    }
    /**
     * 禁止克隆
     */
    private function __clone()
    {
        // TODO: Implement __clone() method.
    }


}