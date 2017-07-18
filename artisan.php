<?php

/**
 * 命令行模式入口文件.
 *
 * composer自动加载
 *
 * illuminate/database数据库扩展
 */
if (PHP_SAPI !== 'cli') {
    return http_response_code(404);
}

//设置时区
ini_set('date.timezone', 'Asia/Shanghai');

//引入入口文件
require __DIR__.'/vendor/autoload.php';

require_once __DIR__.'/config/db.php';

require_once 'app/Console/Handlers.php';

//进入程序
$handle = app(Handlers::class);

//错误记录到日志
$color = app(Command\Console\PrintColor::class);

try {
    $handle->boot($argv);
} catch (Exception $e) {
    print_r($color->getColoredString('Errors:'.$e->getMessage(), 'white', 'red')."\r\n");
    write_log($e->getMessage(), 'ERROR');
}
