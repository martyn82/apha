<?php
declare(strict_types = 1);

namespace Apha\Saga\Annotation;

use Apha\Domain\Identity;
use Apha\Message\Event;
use Apha\Saga\AssociationValues;
use Apha\Saga\Saga;

abstract class AnnotatedSaga extends Saga
{
    /**
     * @var bool
     */
    private $isActive;

    /**
     * @param Identity $identity
     */
    public function __construct(Identity $identity)
    {
        parent::__construct($identity, new AssociationValues([]));
    }

    /**
     * @param Event $event
     * @return void
     */
    public function on(Event $event)
    {
    }

    /**
     * @return bool
     */
    final public function isActive(): bool
    {
        return true;
    }

    /**
     */
    public function start()
    {
        $this->isActive = true;
    }

    /**
     */
    public function end()
    {
        $this->isActive = false;
    }
}