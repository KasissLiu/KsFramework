<?php
/**
 * Created by PhpStorm.
 * User: kasiss
 * Date: 6/12/16
 * Time: 23:45
 */



error_reporting(E_ALL);


define("ROOT_PATH",__DIR__."/../");
define("SYS_PATH",ROOT_PATH."system/");
define("APP_PATH",ROOT_PATH."application/");

require_once SYS_PATH."Bootstrap.php";

$app = new Ksf(new KsfDispatcher());

