<?php

use \Command\Container\Container;

if (! function_exists('app')) {
    /**
     * 容器注入
     *
     * @param $abstract
     * @return mixed
     */
    function app($abstract)
    {
        if (is_null($abstract)) {
            return Container::getInstance();
        }

        return empty($parameters)
            ? Container::getInstance()->make($abstract)
            : Container::getInstance()->makeWith($abstract, $parameters);
    }
}

if(!function_exists('base_path')) {
    function base_path()
    {
        return dirname(__DIR__);
    }
}

if(!function_exists('app_path')) {
    function app_path()
    {
        return dirname(__DIR__).'/app';
    }
}