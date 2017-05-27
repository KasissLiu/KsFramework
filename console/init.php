<?php
/**
 * Created by PhpStorm.
 * User: kasiss
 * Date: 6/12/16
 * Time: 23:45
 */



error_reporting(E_ALL);


!defined("ROOT_PATH") && define("ROOT_PATH",__DIR__."/../");
!defined("SYS_PATH") && define("SYS_PATH",ROOT_PATH."system/");
!defined("APP_PATH") && define("APP_PATH",ROOT_PATH."application/");

require_once SYS_PATH."Bootstrap.php";

$app = new Ksf(new KsfDispatcher());

