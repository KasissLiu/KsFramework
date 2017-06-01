<?php
/**
 *
 * app配置文件
 * array(
 *     "appName"           => "appname",            应用名称
 *     "appLibraryPath"    => APP_PATH."lirary",    类库地址
 *     "appCachePath"      => APP_PATH."cache",     缓存地址
 *     "appDebug"          => true,                 调试模式
 *     "autoloadLibrary"   => array(),              自动加载的类文件
 *     "appModules"        => array(),              应用可用模块
 *     "appRouterModule"   => 1                     应用路由模式
 * )
 *
 * 路由模式
 *      default: 1  sample kasiss.cn/index/index/index?id=1
 *      original: 2 sample kasiss.cn/?r=index/index/index&id=1
 *      rewrite: 3 sample kasiss.cn/index/index/index/id/1
 *
 */

return array(
    "appName"           => "Ksf",
    "appLibraryPath"    => APP_PATH."lirary",
    "appCachePath"      => APP_PATH."cache",

    "appDebug"          => true,
    "autoloadLibrary"   => array(),
    "appModules"        => array(),
    "appRouterModule"   => 2,

    "defaultModule"     => "index",
    "defaultController" => "index",
    "defaultAction"     => "test",

    //smarty config
    "smarty" => array(
        "left_delimiter"=>"{%",
        "right_delimiter"=>"%}",
        "template_dir" => APP_PATH."views/",
        "compiles_root" => APP_PATH."cache/smarty/compiles",
        "cache_root" => APP_PATH."cache/smarty/cache"
    )




);