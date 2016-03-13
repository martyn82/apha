<?php
declare(strict_types = 1);

namespace Apha\Scheduling;

class ScheduleToken
{
    /**
     * @var string
     */
    private $value;

    /**
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getTokenId(): string
    {
        return $this->value;
    }
}