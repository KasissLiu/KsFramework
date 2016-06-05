<?php
/**
 * Created by PhpStorm.
 * User: kasiss
 * Date: 5/26/16
 * Time: 00:02
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
        "passwd"        => "123qwe",
        "dbname"        => "dbname",
        "port"          => 3306,
        "charset"       => "utf8"
    ),
    array(
        "server"        => "server2",
        "type"          => "mysql",
        "host"          => "localhost",
        "user"          => "root",
        "passwd"        => "123qwe",
        "dbname"        => "dbname",
        "port"          => 3306,
        "charset"       => "utf8"
    )
);