<?php
$content = <<<EOF
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

return $content;