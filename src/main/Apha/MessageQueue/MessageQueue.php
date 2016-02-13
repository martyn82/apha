<?php
declare(strict_types = 1);

namespace Apha\MessageQueue;

interface MessageQueue
{
    /**
     * @param string $message
     * @return void
     */
    public function enqueue(string $message);

    /**
     * @return string
     */
    public function dequeue() : string;
}