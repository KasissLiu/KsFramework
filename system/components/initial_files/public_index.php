<?php
$content = <<<EOF
<?php
/**
 * 入口文件 定义基本路径常量 
 * 载入bootstrap文件 
 * 执行Ksf run方法处理web请求
 */
!defined("ROOT_PATH") && define("ROOT_PATH",__DIR__."/../");
!defined("SYS_PATH") && define("SYS_PATH",ROOT_PATH."system/");
!defined("APP_PATH") && define("APP_PATH",ROOT_PATH."application/");

require_once SYS_PATH."Bootstrap.php";

\$app = new Ksf(new KsfDispatcher());

\$app->Bootstrap()->run();        

EOF;

return $content;