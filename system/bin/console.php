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
            if(preg_match('/^([a-z0-9A-Z_\-]+):?([a-z0-9A-Z_\/]*):?([a-z0-9A-z]*)$/' , $argv,$commands)) {
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
        print_r("There is no help info !\n");
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
        echo "this is script help ! \n";
    }

    public function _execute() {
        require_once SYS_PATH."Bootstrap.php";
       
        if(file_exists(CONSOLE_PATH.$this->script.".php")) {
            $app = new Ksf(new KsfDispatcher());
            
            require CONSOLE_PATH.$this->script.".php";
            if(strstr($this->script,'/')) {
                $scripts = explode('/',$this->script);
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
        exec("php ".ROOT_PATH."composer.phar update");
    }


    private function _initFileContent() {
         self::$fileMap["application/model/Sample/Sample/Sample.php"] = <<<EOF
<?php

class Sample_Sample_SampleModel
{



}
EOF;

         self::$fileMap["application/modules/Index/controllers/Error.php"] = <<<EOF
<?php

class ErrorController extends KsfController
{

    public function ErrorAction()
    {
        // do something to show errors
            $e = $this->getError();
            echo $e->getMessage();
    }




}
EOF;
        self::$fileMap["application/modules/Index/controllers/Index.php"] = <<<EOF
<?php

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
          $view = $this->getView();
          $view->display('test.html');

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
    <title>Welcome to Ksf!</title>
</head>
<body>
<h1>Hello Ksf!</h1>

</body>
</html>
EOF;
        self::$fileMap["conf/appConfig.php"] = <<<EOF
<?php
/**
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

EOF;
        self::$fileMap["conf/serverConfig.php"] = <<<EOF
<?php
/**
 *
 * server 配置文件
 * 以mysql配置为例
 * array
 * (
 *  "server"=>, 名称
 *  "type"  =>, 类型
 *  "host"  =>, 地址
 *  "user"  =>, 用户名
 *  "passwd"=>, 密码
 *  "dbname"=>, 数据库名称
 *  "port"  =>, 访问端口号
 *  "charset"=>,使用字符集
 *  )
 *
 *
 */

return array(
    array(
        "server"        => "server1",
        "type"          => "mysql",
        "host"          => "localhost",
        "user"          => "root",
        "passwd"        => "",
        "dbname"        => "dbname",
        "port"          => 3306,
        "charset"       => "utf8",
        "init"          => true,
    ),
    array(
        "server"        => "server2",
        "type"          => "mysql",
        "host"          => "localhost",
        "user"          => "root",
        "passwd"        => "",
        "dbname"        => "dbname",
        "port"          => 3306,
        "charset"       => "utf8",
        "init"          => true,
    ),
    array(
        "server"        => "mailer",
        "type"          => "mail",
        "smtp_server"   => "",
        "smtp_port"     => 465,
        "smtp_ssl"      => "",
        "smtp_username" => "",
        "smtp_password" => "",
        "charset"       => "utf-8",
        "init"          => false,
    )
);

EOF;
        self::$fileMap["console/console.php"] = <<<EOF
<?php

class console extends KsfConsole
{
    public function init()
    {

    }
    public function error()
    {
        $error = Ksf::getInstance()->error;
        print_r($error);
    }
    public function run()
    {
        var_dump(new Sample_Sample_SampleModel());
    }
}
EOF;
        self::$fileMap["public/index.php"] = <<<EOF
<?php

!defined("ROOT_PATH") && define("ROOT_PATH",__DIR__."/../");
!defined("SYS_PATH") && define("SYS_PATH",ROOT_PATH."system/");
!defined("APP_PATH") && define("APP_PATH",ROOT_PATH."application/");

require_once SYS_PATH."Bootstrap.php";

$app = new Ksf(new KsfDispatcher());

$app->Bootstrap()->run();        
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
}



$ksf = new KsfCLI();

