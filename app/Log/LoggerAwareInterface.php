<?php

namespace Command\Log;

interface LoggerAwareInterface
{
    public function setLogger(LoggerInterface $logger);
}
