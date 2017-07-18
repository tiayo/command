<?php

use Command\Console\Command;
use Command\Console\PrintColor;

class Handlers
{
    protected $print_color;
    protected $command;

    public function __construct(PrintColor $printColor, command $command)
    {
        $this->print_color = $printColor;
        $this->command = $command;
    }

    public function boot($argv)
    {
        $function = $argv[1];
        $array = $this->getKey($argv);

        //判断方法是否存在
        if (method_exists($this->command, $function)) {
            return $this->command->$function($array);
        } else {
            return $this->print_color->getColoredString($function.'方法未定义！', 'white', 'red');
        }
    }

    public function getKey($argv)
    {
        $result = [];

        foreach ($argv as $key => $value) {
            if ($this->filter($value)) {
                $value = substr_replace($value, '', 0, 1);
                $result[$value] = $this->filter($argv[$key + 1]) ? null : $argv[$key + 1];
            }
        }

        return $result;
    }

    public function filter($value)
    {
        return substr($value, 0, 1) == '-';
    }
}
