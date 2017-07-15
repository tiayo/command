# PHP命令行

## 介绍
程序基于composer自动加载、laravel database，可以加载到任何程序或独立运行，程序非常简易，可以根据需要进行扩展。

## 配置

运行：`composer install`

运行：`composer dump-auto`

配置数据库文件config/db.php（如果需要连接数据库的话）

## 目录结构

artisan.php：入口文件(返回值输出统一到这个文件)

app：主文件目录

config:配置文件目录

app/Console:命令执行策略目录

app/Container：容器注入策略

app/Controllers：控制器目录

app/Model：Model层

app/helpers.php：辅助函数

app/Console/Command.php：命令执行策略注册文件

app/Console/Handler.php：命令处理文件

app/Console/PrintColor.php：命令输出字体颜色、背景色辅助类