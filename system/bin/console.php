<?php

php_sapi_name() == 'cli' ? : die('NEED CLI ENV!');

!defined("ROOT_PATH") && define("ROOT_PATH",realpath(__DIR__."/../../").'/');
!defined("SYS_PATH") && define("SYS_PATH",realpath(__DIR__."/../").'/');
!defined("APP_PATH") && define("APP_PATH",ROOT_PATH."application/");
!defined("CONSOLE_PATH") && define("CONSOLE_PATH",ROOT_PATH."console/");


class KsfCLI 
{
    private $configs    = array();
    private $command    = null;
    private $parameters = array();
    private $argvs      = null;
    private $argc       = 0;
    private $method     = null;

    private static $commands = array(
        'init' => 'KSF_CONSOLE_INIT',
        'script' => 'KSF_CONSOLE_SCRIPT',
        'db' => 'KSF_CONSOLE_DB',
        'help' => 'KSF_CONSOLE_HELP' 
    );
    private static $legal_configs = array();
    

    public function __construct()
    {
        global $argv,$argc;
    
        $this->argvs = $argv;
        $this->argc = $argc;
        try{
            $this->parse_argv();
            $this->configure_check();
            $this->command_check();
            $this->execute();

        }catch (Exception $e) {
            $message = $e->getMessage() . "\n type  '--help' for help \n";
            $this->do_print($message);
        }
        
    }


    /** parse input */
    private function parse_argv() {
        $argvs = $this->argvs;
        $params = array();
        
        array_shift($argvs);

        if(count($argvs) == 0) {
            // echo help document
            throw new Exception("NO PARAMS INPUT !");
            return;
        }

        foreach($argvs as $argv) {
            //to receive config
            if(preg_match('/\--([a-z0-9A-Z\-_]+)/' , $argv)) {
                $argv = preg_replace('/^--/','',$argv);
                $this->configs[] = strtolower($argv);
                continue;
            }

            //to receive command
            if(preg_match('/^([a-z0-9A-Z_-]+):?([a-z0-9A-Z_\/]*):?([a-z0-9A-z]*)$/' , $argv,$commands)) {
                $this->command[strtolower($commands[1])] = isset($commands[2]) ? strtolower(ltrim($commands[2],':')): '';
                $this->method = isset($commands[3]) ? $commands[3] : "";
                continue;
            }

            //to receive parameters
            if(preg_match('/^([a-z0-9A-Z_]+)\=(.*)/' , $argv ,$params)) {
                $this->parameters[strtolower($params[1])] = isset($params[2]) ? strtolower($params[2]):null;
            }
        }
    }

    /** configure check */
    private function configure_check() {
        if(in_array('help',$this->configs)) {
            $this->command['help'] = $this->command ? key($this->command) :"";
            if(count($this->command) > 1) array_shift($this->command);
            return;
        }
    }

    /** check command legal */
    private function command_check() {

         if(count($this->command) == 0 && key($this->command) != 'help' ) {
            throw new Exception("PLEASE INPUT COMMAND!");
         }
         if(count($this->command)>1){
             throw new Exception("ONLY ONE COMMAND CAN BE EXECUTED!");
         }
         $command = array_shift(array_keys($this->command));
         if(!in_array($command , array_keys(self::$commands))) {
             throw new Exception("NO LEGAL COMMAND FOUND !");
         }
    }

    /** execute  */
    private function execute() {
       $command = $this->command;
       $func = key($command);
       $param = $command[$func];
       
       $class = self::classRemap($func);
       $obj = new $class($param,$this->parameters,$this->method);
       if(method_exists($obj,'_execute')) {
           $obj->_execute();
       }
       
    }

    /** get real class  */
    public static function classRemap($class)
    {
        return self::$commands[$class] ? : null;
    }


    private function do_print($info = array())
    {
        print($info."\n");
    }

}


class KSF_CONSOLE_HELP 
{
    
    public function __construct($command = '') {

        $command = KsfCLI::classRemap($command);
        if( $command && class_exists($command) && $command != strtolower(__CLASS__)) {
            $help = new $command();
            if(method_exists($help,'_help')) {
                $help -> _help();
            }else{
                throw new Exception('OBJECT HAS NO HELP METHOD !');
            }
        }else{
            $this->_help();
        }
    }
    public function _help() {
        $help_info = <<<EOF
Type "--help" for basic help info;

commands:
      init       do a action to deploy all directories and files            
      script     do a script running 
      db         do a db command to manage tables

Type "<command> --help" for command help info;

EOF;
    print_r($help_info."\n");
    }

}

class KSF_CONSOLE_SCRIPT 
{
    private $script = '';
    private $params = array();
    private $method = null;

    public function __construct() {
        $args = func_get_args();
        $this->script = $args[0];
        $this->params = isset($args[1]) ? $args[1] : array();
        $this->method = isset($args[2]) ? $args[2] : "";
    }
    public function _help() {
        $help_info = <<<EOF
type 
     script:<script path>:<mehtod> param=1 .....  
     
     to load a script and run the method typed in. 
     if method is null , 'run' will be the default method.

     the params will pass into the class as Array().
     

EOF;
        print_r($help_info."\n");
    }

    public function _execute() {
        require_once SYS_PATH."Bootstrap.php";
       
        if(file_exists(CONSOLE_PATH.$this->script.".php")) {
            $app = new Ksf(new KsfDispatcher());
            
            require CONSOLE_PATH.$this->script.".php";
            if(strstr($this->script,'/')) {
                $scripts = explode('/',$this->script);
            }else{
                $scripts = array($this->script);
            }

            $app->Bootstrap()->execute(array_pop($scripts),$this->method,$this->params);
        }else{
            throw new Exception ("SCRIPT DO NOT EXISTS! \n");
        }
        
    }
}

/**
 * 
 */
class KSF_CONSOLE_INIT
{
    private static $dirMap = array(
        "application/" => array(),
        "application/cache/" => array(),
        "application/library/" => array(),
        "application/model/" => array(
            "Sample/" => array(
                "Sample/" => array()
            )
        ),
        "application/modules/"=>array(
            "Index/"=> array(
                "controllers/" => array(),
                "views/" => array()
            )
        ),
        "application/views/" => array(
            "index/" => array()
        ),
        "conf/" => array(),
        "console/" => array(),
        "public/" => array(),
    );
    private static $fileMap = array();
    private $config = array(
        "makeDir" => true,
        "writeInitFile" => true,
        "doComposer" => true,
    );

    public function __construct()
    {
    }


    public function _help()
    {
        $help_info = <<<EOF

type "init" 

   choose the configs

   Then the program will make the basic directories and files and help you building an App

EOF;
    print_r($help_info."\n");
    }
    public function _execute()
    {
        $this->_init();
        
        if($this->config['makeDir']) {
            $this->_mkdir(self::$dirMap,ROOT_PATH);
        }
        if($this->config['writeInitFile']) {
            $this->_initFileContent();
            $this->_writeFile(self::$fileMap,ROOT_PATH);
        }
        if($this->config['doComposer']) {
            $this->_doComposer();
        }
    }

    private function _init() {
        $STDIN = fopen("php://stdin",'r+');

        echo "Do you need to init Files ?  Yes/No  ";
        $ismkfile = trim(fread($STDIN,10),"\n");
        $this->config['makeDir'] = in_array(strtolower($ismkfile),array('yes','y') ) ? true : false;
        $this->config['writeInitFile'] = $this->config['makeDir'];

         echo "Do you need to init Composer ?  Yes/No ";
        $isdocomposer = trim(fread($STDIN,10),"\n");
        $this->config['doComposer'] = in_array(strtolower($isdocomposer),array('yes','y') ) ? true : false;
        
        echo "input eny key to start ...\n";
        $start = trim(fread($STDIN,10),"\n");
    }
    
    private function _checkInit() {
        if(!is_writable(ROOT_PATH) || !is_readable(ROOT_PATH)) {
            print_r("Confirm the Directory is Writable and Readable !");
            die();
        }


    }
    private function _mkdir($dirMaps,$prefix) {
        foreach($dirMaps as $dir => $cdir) {
            if(is_dir($prefix.$dir)) {
                print_r($prefix.$dir." ...... Exists! \n");
                continue;
            }
            $result = mkdir($prefix.$dir);
            if($result) {
                print_r($prefix.$dir." ...... created Done! \n");
            }
            if(is_array($cdir) && count($cdir)>0) {
                $this->_mkdir($cdir,$prefix.$dir);
            }
        }
    }

    private function _writeFile($fileMap,$path) {
        foreach($fileMap as $filename => $content) {
            if(!file_exists($path.$filename)) {
                touch($path.$filename);
            }
            $result = file_put_contents($path.$filename,$content);
            if($result) {
                print_r($path.$filename." ...... written Done! \n");
            }
        }
    }

    private function _doComposer() {
        copy(SYS_PATH."components/composer/composer.phar",ROOT_PATH."composer.phar");
        exec("php ".ROOT_PATH."composer.phar install");
    }


    private function _initFileContent() {
                self::$fileMap["public/index.php"] = <<<EOF
<?php
/**
 * 这是测试类 样例 请于项目中删除
 */


 class Example {

     public function __toString(){
         return "this is a class to test autoload library!";
     }
 }
EOF;
                 self::$fileMap['application/Bootstrap.php'] = <<<EOF
<?php
/**
 *
 * 启动类
 * 将在Ksf->bootstrap()时被调用
 * 类内注册的所有 _initXxxx方法将被调用
 */



class Bootstrap
{

    public function _initRouter()
    {
        \$Ksf = Ksf::getInstance();
        \$KsfConfig = KsfConfig::getInstance();

        \$dispatcher = Ksf::getDispatcher();

        \$router = new KsfRouter(\$dispatcher);

        \$router->module = \$router->module ?  \$router->module : \$KsfConfig->get("defaultModule");
        \$router->controller = \$router->controller ? \$router->controller : \$KsfConfig->get("defaultController");
        \$router->action = \$router->action ? \$router->action : \$KsfConfig->get("defaultAction");
        \$Ksf->set('router',\$router);
    }


    public function _initSmarty()
    {
        \$Ksf = Ksf::getInstance();
        \$smarty_config = KsfConfig::getInstance()->get("smarty");

        \$smarty = new Smarty();
        \$smarty->setLeftDelimiter(\$smarty_config["left_delimiter"]);
        \$smarty->setRightDelimiter(\$smarty_config['right_delimiter']);
        \$smarty->setTemplateDir(\$smarty_config["template_dir"]);
        \$smarty->setCompileDir(\$smarty_config["compiles_root"]);
        \$smarty->setCacheDir(\$smarty_config["cache_root"]);


        //自定义template_dir
        \$router = \$Ksf->router;;

        if(is_dir(APP_PATH."modules/".strtolower(\$router->module)."/views/"))
            \$smarty->setTemplateDir(APP_PATH."modules/".strtolower(\$router->module)."/views/");


        \$Ksf->set('render',\$smarty);
    }
    
    public function _initServers()
    {
        \$Ksf = Ksf::getInstance();
        \$servers = KsfConfig::getInstance()->get('servers');
        foreach(\$servers as \$server_name => \$server)
        {
            if(!\$server['init'])
                continue;

            \$server_type = 'server_'.\$server['type'];
            try{
                if(class_exists(\$server_type))
                {
                    \$server_obj = new \$server_type(\$server);
                    \$Ksf->set(\$server_name, \$server_obj);
                }else{ 
                    throw new KsfException('can not make server '.\$server_name);
                }
            }catch( KsfException \$e){
                print_r(\$e->getMessage());
                die();
            }
        }

    }


}
EOF;
         self::$fileMap["application/model/Sample/Sample/Sample.php"] = <<<EOF
<?php
/**
 *  model类
 *  当前样例 会按照 appcation/model/Sample/Sample/Sample.php 加载
 *  请确保文件路径及类文件命名正确
 */
class Sample_Sample_SampleModel
{



}
EOF;

         self::$fileMap["application/modules/Index/controllers/Error.php"] = <<<EOF
<?php
/**
 * 错误接收控制器 
 * 所有类内抛出的错误
 * 都将由modules下controller里的
 * ErrorController ErrorAction 接收
 */

class ErrorController extends KsfController
{

    public function ErrorAction()
    {
        // do something to show errors
            \$e = \$this->getError();
            echo \$e->getMessage();
    }




}
EOF;
        self::$fileMap["application/modules/Index/controllers/Index.php"] = <<<EOF
<?php
/**
 * 默认控制器
 * 如果存在init方法 则init方法会最先执行
 */

class IndexController extends KsfController
{
    public function init()
    {
    }
    public function indexAction()
    {
       echo "hello world!";
    }

    public function testAction()
    {
          \$view = \$this->getView();
          \$view->display('test.html');

    }
}

EOF;

        self::$fileMap["application/modules/Index/views/test.html"] = <<<EOF
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ksf Test</title>
</head>
<body>

<h1>This is a testing page!</h1>

</body>
</html>
EOF;
        self::$fileMap["application/views/index/index.html"] = <<<EOF
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to use Ksf!</title>
</head>
<body>
<h1>Hello Ksf!</h1>

</body>
</html>
EOF;
        self::$fileMap[".env"] = <<<EOF
[app]
;应用名称
appName           = Ksf
;自定义类库路径
appLibraryPath    = APP_PATH/library
;应用缓存路径
appCachePath      = APP_PATH/cache
;应用调试模式
appDebug          = true
;应用路由模式
appRouterModule   = 2

defaultModule     = index
defaultController = index
defaultAction     = test

[smarty]
left_delimiter    = "{%"
right_delimiter   = "%}"
template_dir      = APP_PATH/views/
compiles_root     = APP_PATH/cache/smarty/compiles
cache_root        = APP_PATH/cache/smarty/cache


[env]
configPath        = ROOT_PATH/conf

[server1]
server        = server1
type          = mysqli
host          = localhost
user          = root
passwd        = 
dbname        = dbname
port          = 3306
charset       = utf8
init          = true

[server2]
server        = server1
type          = mysqli
host          = localhost
user          = root
passwd        = 
dbname        = dbname
port          = 3306
charset       = utf8
init          = true
EOF;

        self::$fileMap["console/console.php"] = <<<EOF
<?php
/**
 * 命令行下的php脚本
 * 如果存在init方法 则init方法会最先执行
 * 如果在执行时未指定执行的方法 若存在run方法 则默认执行run()
 * 命令行下传入的参数会以数组的形式传递到执行方法内
 */
class console extends KsfConsole
{
    public function init()
    {

    }
    public function error()
    {
        \$error = Ksf::getInstance()->error;
        print_r(\$error);
    }
    public function run(\$param=array())
    {
        print_r(new Example());
    }
}
EOF;
        self::$fileMap["public/index.php"] = <<<EOF
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

        self::$fileMap["composer.json"] = <<<EOF
{
	"description": "The Kasiss framework",
	"name": "KsFramework",
	"type": "project",
	"homepage": "http://kasiss.cn",
	"require": {
		"php": ">=5.3",
		"smarty/smarty" : "3.1.27"
	}
}
EOF;

        
    }
}

/**
 * 
 */
class KSF_CONSOLE_DB
{
    
    public function __construct()
    {
        # code...
    }

    public function _help()
    {
        $help_info = <<<EOF
Waiting for complete!
EOF;
        print_r($help_info."\n");
    }
}



$ksf = new KsfCLI();

