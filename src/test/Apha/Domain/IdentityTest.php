<?php
declare(strict_types = 1);

namespace Apha\Domain;

class IdentityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function createNewGeneratesNewUUID()
    {
        $instance = Identity::createNew();

        self::assertRegExp(
            '/[abcdef0-9]{8}-[abcdef0-9]{4}-[abcdef0-9]{4}-[abcdef0-9]{4}-[abcdef0-9]{12}/',
            $instance->getValue()
        );

        self::assertEquals($instance->getValue(), (string)$instance);
    }

    /**
     * @test
     */
    public function fromStringInstantiatesNewIdentity()
    {
        $instance = Identity::fromString('foo');
        self::assertInstanceOf(Identity::class, $instance);
    }
}