<?php

namespace Command\Controllers;

use Command\Service\QueueService;

class ArtisanController extends Controller
{
    protected $queue;

    public function __construct(QueueService $queue)
    {
        $this->queue = $queue;
    }

    public function queue($array)
    {
        return $this->queue->queue($array);
    }
}
