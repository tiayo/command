<?php

use \Command\Container\Container;

if (!function_exists('app')) {
    /**
     * 容器注入.
     *
     * @param $abstract
     *
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

if (!function_exists('base_path')) {
    function base_path()
    {
        return dirname(__DIR__);
    }
}

if (!function_exists('app_path')) {
    function app_path()
    {
        return dirname(__DIR__).'/app';
    }
}

if (!function_exists('write_log')) {
    /**
     * 写入文件日志.
     *
     * @param $data
     * @param string $type
     */
    function write_log($data, $type = 'INFO')
    {
        $file = dirname(__DIR__).'/Log/'.date('ymd').'.log';

        if (is_array($data) || is_object($data)) {
            $data = var_export($data, true);
        } elseif (!is_string($data) || !is_numeric($data)) {
            $data = var_export($data, true);
        }

        //加上前缀
        $data = "\r\n".$type.':['.date('Y-m-d H:i:s')."]\r\n".$data;

        //写入
        $log = fopen($file, 'a');

        fwrite($log, $data);

        fclose($log);
    }
}
