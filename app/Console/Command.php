<?php

namespace Command\Console;

use Command\Controllers\ArtisanController;

class Command
{
    protected $artisan;

    public function __construct()
    {
        $this->artisan = app(ArtisanController::class);
    }
    public function queue($argv)
    {
        return $this->artisan->queue($argv);
    }

    public function check($argv)
    {
        return 'check';
    }
}