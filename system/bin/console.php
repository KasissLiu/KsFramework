<?php
php_sapi_name() == 'cli' ?  : die('NEED CLI ENV!');

! defined("ROOT_PATH") && define("ROOT_PATH", realpath(__DIR__ . "/../../") . '/');
! defined("SYS_PATH") && define("SYS_PATH", realpath(__DIR__ . "/../") . '/');
! defined("APP_PATH") && define("APP_PATH", ROOT_PATH . "application/");
! defined("CONSOLE_PATH") && define("CONSOLE_PATH", ROOT_PATH . "console/");

class KsfCLI
{

    private $configs = array();

    private $command = null;

    private $parameters = array();

    private $argvs = null;

    private $argc = 0;

    private $method = null;

    private static $commands = array(
        'init' => 'KSF_CONSOLE_INIT',
        'script' => 'KSF_CONSOLE_SCRIPT',
        'db' => 'KSF_CONSOLE_DB',
        'help' => 'KSF_CONSOLE_HELP',
        'server' => 'KSF_SERVER'
    );
    
    public static $colors = array(
        'black'=>"\e[1;30m",
        'red'=>"\e[1;31m",
        'green'=>"\e[1;32m",
        'yellow'=>"\e[1;33m",
        'blue'=>"\e[1;34m",
        'magenta'=>"\e[1;35m",
        'cyan'=>"\e[1;36m",
        'white'=>"\e[1;37m",
        'normal'=>"\e[m"
    );

    private static $legal_configs = array();
    
    public static function color_msg($msg,$color)
    {
        return isset(self::$colors[$color]) ? self::$colors[$color].$msg.self::$colors['normal'] : $msg;
    }

    public function __construct()
    {
        global $argv, $argc;
        
        $this->argvs = $argv;
        $this->argc = $argc;
        try {
            $this->parse_argv();
            $this->configure_check();
            $this->command_check();
            $this->execute();
        } catch (Exception $e) {
            $message = self::color_msg($e->getMessage(),'red') . "\n";
            $this->do_print($message);
        }
    }

    /**
     * parse input
     */
    private function parse_argv()
    {
        $argvs = $this->argvs;
        $params = array();
        
        array_shift($argvs);
        
        if (count($argvs) == 0) {
            // echo help document
            throw new Exception("NO PARAMS INPUT !");
            return;
        }
        
        foreach ($argvs as $argv) {
            // to receive config
            if (preg_match('/\--([a-z0-9A-Z\-_]+)/', $argv)) {
                $argv = preg_replace('/^--/', '', $argv);
                $this->configs[] = strtolower($argv);
                continue;
            }
            
            // to receive command
            if (preg_match('/^([a-z0-9A-Z_-]+):?([a-z0-9A-Z_\/]*):?([a-z0-9A-z]*)$/', $argv, $commands)) {
                $this->command[strtolower($commands[1])] = isset($commands[2]) ? strtolower(ltrim($commands[2], ':')) : '';
                $this->method = isset($commands[3]) ? $commands[3] : "";
                continue;
            }
            
            // to receive parameters
            if (preg_match('/^([a-z0-9A-Z_]+)\=(.*)/', $argv, $params)) {
                $this->parameters[strtolower($params[1])] = isset($params[2]) ? strtolower($params[2]) : null;
            }
        }
    }

    /**
     * configure check
     */
    private function configure_check()
    {
        if (in_array('help', $this->configs)) {
            $this->command['help'] = $this->command ? key($this->command) : "";
            if (count($this->command) > 1)
                array_shift($this->command);
            return;
        }
    }

    /**
     * check command legal
     */
    private function command_check()
    {
        if ( !$this->command || (count($this->command) == 0 && key($this->command) != 'help')) {
            throw new Exception("PLEASE INPUT COMMAND!");
        }
        if (count($this->command) > 1) {
            throw new Exception("ONLY ONE COMMAND CAN BE EXECUTED!");
        }
        $command = array_keys($this->command);
        $command = array_shift($command);
        if (! in_array($command, array_keys(self::$commands))) {
            throw new Exception("NO LEGAL COMMAND FOUND !");
        }
    }

    /**
     * execute
     */
    private function execute()
    {
        $command = $this->command;
        $func = key($command);
        $param = $command[$func];
        $class = self::classRemap($func);
        $obj = new $class($param, $this->parameters, $this->method);
        if (method_exists($obj, '_execute')) {
            $obj->_execute();
        } else {
           throw new Exception("$func is waiting for complete"); 
        }
    }
    
    public static function KsfInstance()
    {
        if(!file_exists(SYS_PATH . "Bootstrap.php")) {
            throw new Exception("Bootstrap is not found! Please try init first! \n");
        }
        require_once SYS_PATH . "Bootstrap.php";
        
        return new Ksf(new KsfDispatcher());
    }

    /**
     * get real class
     */
    public static function classRemap($class)
    {
        return isset(self::$commands[$class])  ?  self::$commands[$class] : null;
    }

    public function do_print($info = array())
    {
        print_r($info . "\n");
    }
    
}

class KSF_CONSOLE_HELP
{

    private $command;
    
    public function __construct($command = '')
    {
        $this->command = $command;
    }

    public function _help()
    {
        $help_text = <<<EOF
Type "%s" for basic help info;

Avilable Commands:
      %s       do a action to deploy all directories and files
      %s     do a script running 
      %s         do a db command to manage tables
      %s     start a development server

Type "%s" for command help info;

EOF;
        $help_info = sprintf($help_text,
            KsfCLI::color_msg('--help', 'yellow'),
            KsfCLI::color_msg('init', 'green'),
            KsfCLI::color_msg('script', 'green'),
            KsfCLI::color_msg('db', 'green'),
            KsfCLI::color_msg('server', 'green'),
            KsfCLI::color_msg('<command> --help', 'yellow')
        );
        print_r($help_info . "\n");
    }
    
    public function _execute()
    {
        $command = KsfCLI::classRemap($this->command);
        if ($command && class_exists($command) && $command != strtolower(__CLASS__)) {
            $help = new $command();
            if (method_exists($help, '_help')) {
                $help->_help();
            } else {
                throw new Exception("OBJECT HAS NO HELP METHOD ! \n");
            }
        } else {
            $this->_help();
        }
    }
}

class KSF_CONSOLE_SCRIPT
{

    private $script = '';

    private $params = array();

    private $method = null;

    public function __construct()
    {
        $args = func_get_args();
        $this->script = isset($args[0]) ? $args[0] : "";
        $this->params = isset($args[1]) ? $args[1] : array();
        $this->method = isset($args[2]) ? $args[2] : "";
    }

    public function _help()
    {
        $help_text= <<<EOF
type 
     %s:%s:%s %s=1 .....  
     
     to load a script and run the method typed in. 
     if method is null , '%s' will be the default method.

     the params will pass into the class as Array().
     
     script basic path is %s, 
     %s      

EOF;
        $help_info = sprintf($help_text,
            KsfCLI::color_msg('script', 'green'),
            KsfCLI::color_msg('<script path>', 'magenta'),
            KsfCLI::color_msg('<mehtod>', 'blue'),
            KsfCLI::color_msg('param', 'yellow'),
            KsfCLI::color_msg('run', 'yellow'),
            KsfCLI::color_msg('ROOT_PATH/console', 'red'),
            KsfCLI::color_msg('please confirm the file is put in right place! ', 'red')
        );
        print_r($help_info . "\n");
    }

    public function _execute()
    {
        
        $app = KsfCLI::KsfInstance();
        
        if (!file_exists(CONSOLE_PATH . $this->script . ".php")) {
                throw new Exception("SCRIPT DO NOT EXISTS! \n");
        }
        require CONSOLE_PATH . $this->script . ".php";
        if (strstr($this->script, '/')) {
            $scripts = explode('/', $this->script);
        } else {
            $scripts = array(
                $this->script
            );
        }
        
        $app->Bootstrap()->execute(array_pop($scripts), $this->method, $this->params);
    }
}

/**
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
        "application/modules/" => array(
            "Index/" => array(
                "controllers/" => array(),
                "views/" => array()
            )
        ),
        "application/views/" => array(
            "index/" => array()
        ),
        "conf/" => array(),
        "console/" => array(),
        "public/" => array()
    );

    private static $fileMap = array();

    private $config = array(
        "makeDir" => true,
        "writeInitFile" => true,
        "doComposer" => true
    );

    public function __construct()
    {}

    public function _help()
    {
        $help_text = <<<EOF

type "init" 

   choose the %s

   Then the program will make the basic directories and files 
   to help you building an App in %s directory

EOF;
        $help_info = sprintf($help_text,
            KsfCLI::color_msg('configs', 'yellow'),
            KsfCLI::color_msg('current', 'red')
        );
        print_r($help_info . "\n");
    }

    public function _execute()
    {
        $this->_init();
        
        if ($this->config['makeDir']) {
            $this->_mkdir(self::$dirMap, ROOT_PATH);
        }
        if ($this->config['writeInitFile']) {
            $this->_initFileContent();
            $this->_writeFile(self::$fileMap, ROOT_PATH);
        }
        if ($this->config['doComposer']) {
            $this->_doComposer();
        }
    }

    private function _init()
    {
        $STDIN = fopen("php://stdin", 'r+');
        
        echo "Do you need to init Files ?  Yes/No  ";
        $ismkfile = trim(fread($STDIN, 10), "\n");
        $this->config['makeDir'] = in_array(strtolower($ismkfile), array(
            'yes',
            'y'
        )) ? true : false;
        $this->config['writeInitFile'] = $this->config['makeDir'];
        
        echo "Do you need to init Composer ?  Yes/No ";
        $isdocomposer = trim(fread($STDIN, 10), "\n");
        $this->config['doComposer'] = in_array(strtolower($isdocomposer), array(
            'yes',
            'y'
        )) ? true : false;
        
        echo "input eny key to start ...\n";
        $start = trim(fread($STDIN, 10), "\n");
    }

    private function _checkInit()
    {
        if (! is_writable(ROOT_PATH) || ! is_readable(ROOT_PATH)) {
            throw new Exception("Confirm the Directory is Writable and Readable !");
            die();
        }
    }

    private function _mkdir($dirMaps, $prefix)
    {
        foreach ($dirMaps as $dir => $cdir) {
            if (is_dir($prefix . $dir)) {
                print_r($prefix . $dir . " ...... Exists! \n");
                continue;
            }
            $result = mkdir($prefix . $dir);
            if ($result) {
                print_r($prefix . $dir . " ...... created Done! \n");
            }
            if (is_array($cdir) && count($cdir) > 0) {
                $this->_mkdir($cdir, $prefix . $dir);
            }
        }
    }

    private function _writeFile($fileMap, $path)
    {
        foreach ($fileMap as $filename => $content) {
            if (! file_exists($path . $filename)) {
                touch($path . $filename);
            }
            $result = file_put_contents($path . $filename, $content);
            if ($result) {
                print_r($path . $filename . " ...... written Done! \n");
            }
        }
    }

    private function _doComposer()
    {
        copy(SYS_PATH . "components/composer/composer.phar", ROOT_PATH . "composer.phar");
        exec("php " . ROOT_PATH . "composer.phar install");
    }

    private function _initFileContent()
    {
        self::$fileMap['application/Bootstrap.php'] = require (SYS_PATH . '/components/initial_files/bootstrap.php');
        self::$fileMap["application/model/Sample/Sample/Sample.php"] = require (SYS_PATH . '/components/initial_files/model_sample.php');
        
        self::$fileMap["application/modules/Index/controllers/Error.php"] = require (SYS_PATH . '/components/initial_files/controller_error.php');
        self::$fileMap["application/modules/Index/controllers/Index.php"] = require (SYS_PATH . '/components/initial_files/controller_index.php');
        
        self::$fileMap["application/modules/Index/views/test.html"] = require (SYS_PATH . '/components/initial_files/test_html.php');
        self::$fileMap["application/views/index/index.html"] = require (SYS_PATH . '/components/initial_files/index_html.php');
        self::$fileMap[".env.example"] = require (SYS_PATH . '/components/initial_files/env.php');
        
        self::$fileMap["console/console.php"] = require (SYS_PATH . '/components/initial_files/console.php');
        self::$fileMap["public/index.php"] = require (SYS_PATH . '/components/initial_files/public_index.php');
        
        self::$fileMap["composer.json"] = require (SYS_PATH . '/components/initial_files/composer.php');
    }
}

class KSF_SERVER
{
    private $script;
    private $params;
    

    public function __construct()
    {
        $args = func_get_args();
        $this->script = isset($args[0]) ? $args[0] : "";
        $this->params = isset($args[1]) ? $args[1] : array();
    }
    
    public function _help()
    {
        $help_info = <<<EOF
        type %s %s start a server at port <port> default %s \n
EOF;
        $help_info = sprintf($help_info,
            KsfCLI::color_msg('server', 'cyan'),
            KsfCLI::color_msg('port=<port>', 'cyan'),
            KsfCLI::color_msg('8888', 'cyan')
            );
        
        print_r($help_info);
    }

    public function _execute()
    {
        
        $port = 8888;
        
        isset($this->params['port']) && $this->params['port'] > 0 && $port = $this->params['port'];
        
        if($port < 1000) {
            print_r(KsfCLI::color_msg("server will start at port $port ,make sure you have right access! \n",'yellow'));
        }
        
        if (file_exists(ROOT_PATH . 'public')) {
            print_r(KsfCLI::color_msg('kasiss development server started:', 'green')." <http://127.0.0.1:$port>\n");
            exec("php -S 0.0.0.0:$port -t " . ROOT_PATH . "public", $op);
        } else {
            throw new Exception('Path ./public not exists! Please try \'./system/bin/console init\' first! ');
        }
    }
}

/**
 */
class KSF_CONSOLE_DB
{
    private $action;
    private $param;
    private $app;
    private $server;
    private $server_config;
    private $database;
    
    private $struct_path = 'struct/';
    private $data_path = 'data/';
    private $actions = array('dump','load');
    
    
    public function __construct()
    {
        // code...
        $params = func_get_args();
        $this->action = isset($params[0]) ? $params[0] : "";
        $this->param = isset($params[1]) ? $params[1] : "";
        
    }

    public function _help()
    {
        $help_info = <<<EOF
type 
     %s %s %s  dump data from database
     %s %s %s  load data from file to database
            
params 
     table   = "all" / ""
     version = <YmdHis>
     data    = true
     server  = <first mysqli server>
     dbname  = <first mysqli server dbname>
       
EOF;
        $help_info = sprintf($help_info,
            KsfCLI::color_msg('db:dump', 'magenta'),
            KsfCLI::color_msg('[param_name=<value>]', 'cyan'),
            KsfCLI::color_msg('db:load', 'magenta'),
            KsfCLI::color_msg('[param_name=<value>]', 'cyan')
            );
        
        print_r($help_info . "\n");
    }
    
    public function _execute()
    {
        $action = $this->action;
        $param = $this->param;
        $table = (isset($param['table']) && $param['table']) ? explode(',',trim($param['table'],',')) : [];
        $version = (isset($param['version']) && $param['version']) ? $param['version'] : date('YmdHis');
        $dataDump = isset($param['data']) ? $param['data'] : true;
        $serverName = (isset($param['server']) && $param['server']) ? $param['server'] : '';
        $database = (isset($param['dbname']) && $param['dbname']) ? $param['dbname'] :  "";
        
        if(!in_array($action,$this->actions)) {
            throw new Exception("db:{$action} is no available!");
        }
        
        if(!file_exists(SYS_PATH."Bootstrap.php")) {
            throw  new Exception('');
        }
        
        $this->app = KsfCLI::KsfInstance();
        
        
        
        $servers = KsfConfig::getInstance()->get("servers");
        if(
            $serverName 
            && isset($servers[$serverName]) 
            && isset($servers[$serverName]['type']) 
            && $servers[$serverName]['type']=='mysqli'
        ) {
            $this->server_config = $servers[$serverName];
        } else {
          foreach($servers as $key => $val) {
              if( $val['type'] == 'mysqli') {
                  $this->server_config = $servers[$key];
                  break;
              }
          }
        }
        
        $this->database = $database ? $database : $this->server_config['dbname'];
        
        $this->server = new server_mysqli($this->server_config);

        $this->$action($table,$version,$dataDump);
        
    }
    
    private function get_tables()
    {
        $sql = "show tables";
        $result = $this->server->rawQuery($sql);
        $tables = array();
        foreach($result as $value) {
            $tables[] = array_shift($value);
        }
        
        return $tables;
    }
    
    private function get_table_info($table) {
        $sql = "show create table `$table`";
        $result = $this->server->rawQuery($sql);
        if($result) {
            return $result[0]["Create Table"];
        }
        
        return "";
        
    }
    
    private function get_struct($table)
    {
        $sql = "select COLUMN_NAME,COLUMN_TYPE from INFORMATION_SCHEMA.Columns where table_name='$table' and table_schema='easyfans'";
        $result = $this->server->rawQuery($sql);
        $returns = array();
        foreach($result as $value) {
            $returns[$value['COLUMN_NAME']] = $value['COLUMN_TYPE'];
        }
        return $returns;
    }
    
    private function show_primary_key($table) {
        $sql = "show keys from `$table` where key_name = 'primary'";
        $result = $this->server->rawQuery($sql);
        return $result ? $result[0]['Column_name'] : "";
    }
    
    private function patch_table_name($table,$version)
    {
        return $table.'_'.$version.'.sql';
    }
    
    private function patch_migration_path()
    {
        $dbname = $this->database;
        return ROOT_PATH.'storage/migration/'.$dbname.'/';
    }
    
    private function dump($table,$version,$dataDump) {
        $migration_dir = $this->patch_migration_path();
        if(!is_dir($migration_dir)) {
            if(mkdir($migration_dir,0777,true)) {
                print_r("create path $migration_dir \n");
            }else {
                throw new Exception("make dir '$migration_dir' error \n");
            }
        }
        
        $tables = $this->get_tables();
        if($table) {
            foreach($table as $value) {
                if(!in_array($value, $tables)) {
                    print_r(KsfCLI::color_msg("Table $value is not exitst ! \n", "red"));
                    return;
                }
            }
        } else {
            $table = $tables;
        }  
        //do struct save
        $this->save_struct($table, $version, $migration_dir);
        //do data save
        $this->save_data($table, $version, $migration_dir);
       
        
    }
    
    private function save_struct($table,$version,$migration_dir)
    {
        $struct_path = $migration_dir.$this->struct_path;
        if(!is_dir($struct_path)) {
            mkdir($struct_path,0777,true);
            print_r("create path $struct_path \n");
        }
        foreach($table as $value) {
            $table_info = $this->get_table_info($value);
            $filename = $this->patch_table_name($value, $version);
            $res = file_put_contents($struct_path.$filename, $table_info);
            if($res) {
                print_r(
                    "struct save table:".
                    KsfCLI::color_msg($value,'magenta')." version:".
                    KsfCLI::color_msg($version, 'cyan')."......".
                    KsfCLI::color_msg('Done', 'green')."! \n"
                );
            }
        }
    }
    
    private function save_data($table,$version,$migration_dir) {
        $data_path = $migration_dir.$this->data_path;
        if(!is_dir($data_path)) {
            mkdir($data_path,0777,true);
            print_r("create path $data_path \n");
        }
        
        foreach($table as $value) {
            $struct = $this->get_struct($value);
            print_r(
                "dump data table:".
                KsfCLI::color_msg($value, 'magenta').
                "  version ".
                KsfCLI::color_msg($version, 'cyan').
                "......"
            );
            $filename = $this->patch_table_name($value, $version);
            $f = fopen($data_path.$filename, "w");
            $start = 0;
            $limit = 100;
            while(true) {
                $sql = "select * from $value limit $start,$limit";
                $result = $this->server->rawQuery($sql);
                if(!$result) {
                    break;
                }
                $insert = $this->trans_to_insert($value, $result, $struct);
                fputs($f, $insert);
                $start += $limit;
            }
            fclose($f);
            print_r(KsfCLI::color_msg('Done', 'green')."\n");
        }
        
    }
    
    private function trans_to_insert($table,$result,$struct) {
        $string = "INSERT INTO `$table` VALUES ";
        foreach($result as $value) {
            $tmp = "(";
            foreach($value as $key =>$val) {
                if(
                    strstr($struct[$key],'int')
                    || strstr($struct[$key],'decimal')
                    || strstr($struct[$key],'float')
                    || strstr($struct[$key],'double')
                    ) {
                        $tmp .= $val.',';
                    }else {
                        $tmp .= '\''.$val.'\',';
                    }
            }
            $tmp = rtrim($tmp,',').'),';
            $string .= $tmp;
        }
        $string = rtrim($string,',').";\n";
        return $string;
    }
    
    
    private function load($table,$version) {
        //check table is exists
        $migration_path = $this->patch_migration_path();
        $table = $table ? : $this->get_tables();
        //get struct files 
        $need_load_struct = $this->get_need_load_files($version, $migration_path,$this->struct_path);
        //get data files
        $need_load_data = $this->get_need_load_files($version, $migration_path, $this->data_path);
        $this->load_data($need_load_data, $migration_path);
    }
    
    private function get_need_load_files($version,$migration_path,$type_path)
    {
        $struct_path = $migration_path.$type_path;
        $files = scandir($struct_path);
        $need_load_files = array();
        foreach($files as $value) {
            if(strstr($value,$version)) {
                $need_load_files[] = $value;
            }
        }
        return $need_load_files;
    }
    
    private function load_struct($filename,$migration_path)
    {
        $struct_path = $migration_path.$this->struct_path;
        $file = $struct_path.$filename;
        if(file_exists($file)) {
            $sql = file_get_contents($file);
            $this->server->query($sql);
            return true;
        }else {
            print_r(KsfCLI::color_msg($filename,'cyan')." is ".KsfCLI::color_msg('missed', 'red'));
            return false;
        }
    }
    
    private function load_data($data_files,$migration_path)
    {
        $tables = $this->get_tables();
        $data_path = $migration_path.$this->data_path;
        foreach($data_files as $file) {
           $tablename = $this->get_table_name($file);
           if(!in_array($tablename, $tables)) {
               $this->load_struct($file, $migration_path);
           }
           $filename = $data_path.$file;
           print_r("loading data ".KsfCLI::color_msg($filename, 'cyan')." ...." );
           
           if(file_exists($filename)) {
               $f = fopen($filename, 'r');
               while( ($row = fgets($f)) == true ) {
                   $sql = trim($row,"\n");
                   $res = $this->server->query($sql);
               }
               fclose($f);
               print_r(KsfCLI::color_msg('Done', 'green')."\n");
           } else {
               print_r(KsfCLI::color_msg('Error', 'red')."!\n");
           }
        }
        
        print_r(KsfCLI::color_msg("load data Done",'green')."\n");
    }
    
    private function get_table_name($filename) 
    {
        $res = '/^([a-zA-Z0-9_]+)_{1}.*/';
        $matches = null;
        preg_match($res, $filename,$matches);
        if($matches) {
            return $matches[1];
        }
        return null;
        
    }
    
}

$ksf = new KsfCLI();

