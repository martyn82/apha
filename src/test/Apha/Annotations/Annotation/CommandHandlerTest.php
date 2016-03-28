<?php
declare(strict_types = 1);

namespace Apha\Annotations\Annotation;

class CommandHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \Apha\Annotations\ReadOnlyException
     */
    public function setMethodNameIsWriteOnce()
    {
        $annotation = new CommandHandler();
        $annotation->setMethodName('bar');
        $annotation->setMethodName('baz');
    }

    /**
     * @test
     * @expectedException \Apha\Annotations\ReadOnlyException
     */
    public function setCommandTypeIsWriteOnce()
    {
        $annotation = new CommandHandler();
        $annotation->setCommandType('bar');
        $annotation->setCommandType('baz');
    }
}
