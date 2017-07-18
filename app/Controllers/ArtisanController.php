<?php

namespace Command\Controllers;

use Command\Service\QueueService;

class ArtisanController extends Controller
{
    protected $queue;

    public function __construct()
    {
        $this->queue = app(QueueService::class);
    }

    public function queue($array)
    {
        return $this->queue->queue($array);
    }

}