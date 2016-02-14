<?php
declare(strict_types=1);

namespace Apha\EventStore;

use PhpCollection\Map;

class EventClassMap
{
    /**
     * @var Map
     */
    private $innerMap;

    /**
     * @param array $eventClasses
     */
    public function __construct(array $eventClasses)
    {
        $this->innerMap = new Map();

        array_walk(
            $eventClasses,
            function (string $eventClass) {
                $this->add($eventClass);
            }
        );
    }

    /**
     * @param string $eventClass
     */
    private function add(string $eventClass)
    {
        $this->innerMap->set(
            $eventClass::getName(),
            $eventClass
        );
    }

    /**
     * @param string $name
     * @return string
     * @throws \OutOfBoundsException
     */
    public function getClassByEventName(string $name)
    {
        if (!$this->innerMap->containsKey($name)) {
            throw new \OutOfBoundsException("Event class not mapped: '{$name}'.");
        }

        return $this->innerMap->get($name)->get();
    }
}