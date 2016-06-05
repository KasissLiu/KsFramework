<?php
/**
 * Created by PhpStorm.
 * User: kasiss
 * Date: 5/25/16
 * Time: 23:40
 *
 * app配置文件
 * array(
 *     "appName"           => "kasiss",             应用名称
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
    "appName"           => "kasiss",
    "appLibraryPath"    => APP_PATH."lirary",
    "appCachePath"      => APP_PATH."cache",

    "appDebug"          => true,
    "autoloadLibrary"   => array(),
    "appModules"        => array(),
    "appRouterModule"   => 1,

    "defaultModule"     => "Index",
    "defaultController" => "index",
    "defaultAction"     => "index"



);