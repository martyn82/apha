<?php
declare(strict_types = 1);

namespace Apha\Message;

class MessagesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function constructAcceptsArrayWithMessages()
    {
        $message = new MessagesTest_Message();

        $messages = [
            $message
        ];

        $instance = new Messages($messages);
        self::assertCount(count($messages), $instance->getIterator());
    }

    /**
     * @test
     * @expectedException \TypeError
     */
    public function constructRaisesErrorIfNotAllElementsAreEvent()
    {
        $messages = [
            new \stdClass()
        ];

        new Messages($messages);
    }

    /**
     * @test
     */
    public function addAddsMessageToList()
    {
        $instance = new Messages();
        $instance->add(new MessagesTest_Message());

        self::assertCount(1, $instance->getIterator());
    }

    /**
     * @test
     */
    public function clearMakesListEmpty()
    {
        $instance = new Messages([
            new MessagesTest_Message()
        ]);
        $instance->clear();

        self::assertCount(0, $instance->getIterator());
    }

    /**
     * @test
     */
    public function sizeReturnsNumberOfMessages()
    {
        $instance = new Messages([
            new MessagesTest_Message()
        ]);

        self::assertEquals(1, $instance->size());
    }
}

class MessagesTest_Message implements Message
{
}