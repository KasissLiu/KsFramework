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
    // 环境文件解析
    private $env = array();
    // 配置文件目录
    private $configPath = "";
    // 应用名称
    private $appName = "";
    // 应用类库
    private $appLibraryPath = "";
    // 应用缓存地址
    private $appCachePath = "";
    // 应用调试模式
    private $appDebug = false;
    // 应用路由模式
    private $appRouterModule = KsfRouter::DEFAULT_MODE;
    // 应用所使用的链接配置
    private $servers = array();
    // 存储单例
    private static $_instance;

    /**
     * KsfConfig constructor.
     * 初始化appconfig 文件
     * 初始化serverconfig 文件
     */
    public function __construct()
    {
        $this->_initEnv();
        $this->_initAppConfig();
        $this->_initServerConfig();
        $this->_initConstantConfig();
        $this->_initCustomConfig();
    }

    /**
     * 加载环境配置 .
     *
     *
     * env
     */
    private function _initEnv()
    {
        $this->env = KsfLoader::$env;
    }

    /**
     * 加载app配置文件
     * 加载失败后 抛出异常
     *
     * @throws Exception
     */
    private function _initAppConfig()
    {
        if (! isset($this->env['app'])) {
            throw new KsfException('APP CONFIGURE IS MISSING !');
        }
        $app_config = $this->env['app'];
        foreach ($app_config as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * 加载server配置文件
     * 加载失败时 抛出异常
     *
     * @throws Exception
     */
    private function _initServerConfig()
    {
        foreach ($this->env as $key => $value) {
            if (strstr($key, 'server')) {
                $this->servers[$key] = $value;
            }
        }
    }

    /**
     * 定义配置文件中的常量
     */
    private function _initConstantConfig()
    {
        $constants = isset($this->env['constant']) ? $this->env['constant'] : array();
        if ($constants) {
            foreach ($constants as $conName => $conValue) {
                $conName = strtoupper($conName);
                ! defined($conName) && define($conName, $conValue);
            }
        }
        return true;
    }

    /**
     * 加载自定义配置
     */
    private function _initCustomConfig()
    {
        foreach ($this->env as $key => $value) {
            if ($key == 'app' || strstr($key, 'server' || $key == 'constant')) {
                continue;
            }
            $this->set($key, $value);
        }
    }

    /**
     * 用户可自定义添加配置
     *
     * @param $prop
     * @param $value
     */
    public function set($prop, $value)
    {
        $this->$prop = $value;
    }

    /**
     * 用户通过属性名 获取配置内容
     *
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
     *
     * @param $serverName
     * @return null
     */
    public function getServerConfig($serverName)
    {
        return isset($this->servers[$serverName]) ? $this->servers[$serverName] : null;
    }

    /**
     * 单例工厂
     * 返回配置实例本身
     *
     * @return KsfConfig
     */
    public static function getInstance()
    {
        if (! (self::$_instance instanceof self))
            self::$_instance = new self();
        return self::$_instance;
    }

    /**
     * 自定义载入配置文件
     */
    public static function import($config_file, $prop_name = null)
    {
        $instance = self::getInstance();
        
        $config_file = trim($config_file, '.php') . '.php';
        if (file_exists(CONFIG_PATH . $config_file)) {
            $user_config = require_once (CONFIG_PATH . $config_file);
        } else {
            throw new KsfException("Config File Not Found!");
        }
        
        if (is_array($user_config)) {
            foreach ($user_config as $prop => $value) {
                if ($prop_name) {
                    if ($prop_name == $prop) {
                        $instance->set($prop, $value);
                        break;
                    }
                } else {
                    $instance->set($prop, $value);
                }
            }
        }
        
        return $prop_name ? $instance->get($prop_name) : $user_config;
    }

    /**
     * 禁止克隆
     */
    private function __clone()
    {
        // TODO: Implement __clone() method.
    }
}