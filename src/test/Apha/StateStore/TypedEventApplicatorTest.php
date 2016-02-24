<?php
declare(strict_types = 1);

namespace Apha\StateStore;

use Apha\Message\Event;

class TypedEventApplicatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function applyTypedEvent()
    {
        $document = new TypedEventApplicator_Document('1', 1);
        $document->apply(new TypedEventApplicator_Event());

        self::assertTrue($document->typedEventApplied());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function applyTypedEventWillThrowExceptionIfEventCannotBeApplied()
    {
        $document = new TypedEventApplicator_Document('1', 1);
        $document->apply(new class extends Event {});
    }
}

class TypedEventApplicator_Document implements Document
{
    use TypedEventApplicator;

    /**
     * @var string
     */
    private $id;

    /**
     * @var int
     */
    private $version;

    /**
     * @var bool
     */
    private $typedEventApplied = false;

    /**
     * @param string $id
     * @param int $version
     */
    public function __construct(string $id, int $version)
    {
        $this->id = $id;
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @param TypedEventApplicator_Event $event
     */
    protected function applyTypedEventApplicator_Event(TypedEventApplicator_Event $event)
    {
        $this->typedEventApplied = true;
    }

    /**
     * @return bool
     */
    public function typedEventApplied(): bool
    {
        return $this->typedEventApplied;
    }
}

class TypedEventApplicator_Event extends Event
{
}