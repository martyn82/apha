<?php
declare(strict_types = 1);

namespace Apha\Annotations;

use Apha\Message\Message;

/**
 * @group annotations
 */
class DefaultParameterResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function resolveParameterValueRetrievesValueFromMessage()
    {
        $message = new DefaultParameterResolverTest_Message();
        $resolver = new DefaultParameterResolver();
        $value = $resolver->resolveParameterValue($message, 'valueToGet');

        self::assertEquals('foo', $value);
    }

    /**
     * @test
     */
    public function resolveParameterValueRetrievesValueFromMessageInherited()
    {
        $message = new DefaultParameterResolverTest_Event();
        $resolver = new DefaultParameterResolver();
        $value = $resolver->resolveParameterValue($message, 'valueToGet');

        self::assertEquals('foo', $value);
    }

    /**
     * @test
     * @expectedException \Apha\Annotations\UnresolvableParameterException
     */
    public function resolveParameterValueForUnresolvableParameterThrowsException()
    {
        $message = new DefaultParameterResolverTest_Message();
        $resolver = new DefaultParameterResolver();
        $resolver->resolveParameterValue($message, 'unresolvable');
    }
}

class DefaultParameterResolverTest_Message extends Message
{
    /**
     * @var string
     */
    protected $valueToGet = 'foo';
}

class DefaultParameterResolverTest_Event extends DefaultParameterResolverTest_Message
{
}
