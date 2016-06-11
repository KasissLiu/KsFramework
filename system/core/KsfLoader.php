<?php
/**
 * Created by PhpStorm.
 * User: kasiss
 * Date: 6/2/16
 * Time: 23:15
 */

class KsfLoader
{

    public static $instance;

    public static $module;
    public static $controller;

    /**
     * KsfLoader constructor.
     * 初始化必要的全局常量
     * 并注册自动加载函数
     */
    public function __construct()
    {
        !defined("APP_PATH") && define("APP_PATH" , '../../application/');
        !defined("APP_LIBRARY") && define("APP_LIBRARY" , APP_PATH.'library/');

        !defined("SYS_PATH") && define("SYS_PATH" , '../../system/');
        !defined("SYS_CORE") && define("SYS_CORE" , SYS_PATH.'core/');
        !defined("SYS_LIBRARY") && define("SYS_LIBRARY" , SYS_PATH.'library/');


        $this->composer_autoload();

        spl_autoload_register(array("KsfLoader","sysAutoLoader"));
        spl_autoload_register(array("KsfLoader","appAutoLoader"));

    }

    /**
     * 加载composer 类库
     */
    public function composer_autoload()
    {
        if(file_exists(SYS_PATH.'/../vendor/autoload.php'))
            include_once SYS_PATH.'/../vendor/autoload.php';
    }

    /**
     * 文件加载函数
     * @param $file
     * @return bool
     */
    public static function import($file)
    {
        if(file_exists($file))
        {
            include_once $file;
            return true;
        }else{
            throw  new Exception("Loader: {$file}  is not exist");
        }
    }

    /**
     * 系统类 自动加载函数
     * @param $class
     * @throws Exception
     */
    public static function sysAutoLoader($class)
    {
        if(file_exists(SYS_CORE.$class.'.php')) {
            include_once(SYS_CORE.$class.'.php');
        }else{
            throw new Exception("Class {$class} File is Not Found!");
        }
    }

    /**
     * 应用资源库 自动加载函数
     * @param $class
     */
    public static function appAutoLoader($class)
    {
        if(file_exists(APP_LIBRARY.$class.'.php')) {
            include_once(APP_LIBRARY.$class.'.php');
        }else{
            throw new Exception("Class {$class} File is Not Found!");
        }
    }

    public static function getInstance()
    {
        if(empty(self::$instance))
        {
            self::$instance = new self;
        }
        return self::$instance;
    }

}

return KsfLoader::getInstance();