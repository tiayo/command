<?php

use Illuminate\Database\Capsule\Manager as Capsule;

$config = include(dirname(__DIR__).'/../Application/Common/Conf/config.php');

$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $config['DB_HOST'],
    'database'  => $config['DB_NAME'],
    'username'  => $config['DB_USER'],
    'password'  => $config['DB_PWD'],
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => $config['DB_PREFIX'],
]);

// Set the event dispatcher used by Eloquent models... (optional)
use Illuminate\Events\Dispatcher;

use Illuminate\Container\Container;

$capsule->setEventDispatcher(new Dispatcher(new Container));

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();

// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();