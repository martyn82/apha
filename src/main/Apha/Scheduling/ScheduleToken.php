<?php
declare(strict_types = 1);

namespace Apha\Scheduling;

use JMS\Serializer\Annotation as Serializer;

class ScheduleToken
{
    /**
     * @Serializer\Type("string")
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