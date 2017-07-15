<?php

/**
 * 命令行模式入口文件
 *
 * composer自动加载
 *
 * illuminate/database数据库扩展
 *
 */

error_reporting(E_ALL^E_NOTICE^E_WARNING);

if (PHP_SAPI !== "cli") {
    return http_response_code(404);
}

//引入入口文件
require __DIR__.'/vendor/autoload.php';

require_once __DIR__.'/config/db.php';

require_once 'app/Console/Handlers.php';

//开始执行
$handle = new \Command\Console\Handlers();

print_r($handle->boot($argv)."\r\n");

