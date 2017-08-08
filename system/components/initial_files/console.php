<?php

$content = <<<EOF
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


return $content;