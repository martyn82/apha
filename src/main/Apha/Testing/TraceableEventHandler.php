<?php
declare(strict_types = 1);

namespace Apha\Testing;

interface TraceableEventHandler
{
    /**
     * @return array
     */
    public function getEvents(): array;

    /**
     * @return void
     */
    public function clearTraceLog();
}
