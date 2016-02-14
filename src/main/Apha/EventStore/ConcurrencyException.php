<?php
declare(strict_types = 1);

namespace Apha\EventStore;

class ConcurrencyException extends \RuntimeException
{
    /**
     * @var string
     */
    private static $messageTemplate = "Playhead was expected to be '%d' but is '%d'.";

    /**
     * @param int $expectedPlayHead
     * @param int $actualPlayHead
     */
    public function __construct(int $expectedPlayHead, int $actualPlayHead)
    {
        parent::__construct(
            sprintf(
                static::$messageTemplate,
                $expectedPlayHead,
                $actualPlayHead
            )
        );
    }
}