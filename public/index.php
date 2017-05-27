<?php
/**
 * Created by PhpStorm.
 * User: kasiss
 * Date: 5/24/16
 * Time: 22:43
 */
error_reporting(E_ALL);


!defined("ROOT_PATH") && define("ROOT_PATH",__DIR__."/../");
!defined("SYS_PATH") && define("SYS_PATH",ROOT_PATH."system/");
!defined("APP_PATH") && define("APP_PATH",ROOT_PATH."application/");

require_once SYS_PATH."Bootstrap.php";

$app = new Ksf(new KsfDispatcher());

$app->Bootstrap()->run();
