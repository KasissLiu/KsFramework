<?php
/**
 * Created by PhpStorm.
 * User: kasiss
 * Date: 5/24/16
 * Time: 22:43
 */
error_reporting(E_ALL);


define("ROOT_PATH",__DIR__."/../");
define("SYS_PATH",ROOT_PATH."system/");
define("APP_PATH",ROOT_PATH."application/");

require_once SYS_PATH."Bootstrap.php";

$test = new Ksf(new KsfDispatcher());

$test->Bootstrap()->run();
