<?php
declare(strict_types = 1);

namespace Apha\Domain\Annotation;

use Apha\Annotations\AggregateIdentifierAnnotationReader;
use Apha\Annotations\Annotation\AggregateIdentifier;
use Apha\Domain\AggregateRoot;
use Apha\Domain\Identity;

abstract class AnnotatedAggregateRoot extends AggregateRoot
{
    /**
     * @var AggregateIdentifier
     */
    private $aggregateIdentifier;

    /**
     * @throws AmbiguousAggregateIdentifierException
     * @throws NoAggregateIdentifierException
     */
    private function readAnnotatedAggregateIdentifier()
    {
        $reader = new AggregateIdentifierAnnotationReader($this);
        $annotations = $reader->readAll();

        if (empty($annotations)) {
            throw new NoAggregateIdentifierException($this);
        }

        if (count($annotations) > 1) {
            throw new AmbiguousAggregateIdentifierException($this);
        }

        $this->aggregateIdentifier = $annotations[0];
    }

    /**
     * @return Identity
     */
    final public function getId(): Identity
    {
        if (empty($this->aggregateIdentifier)) {
            $this->readAnnotatedAggregateIdentifier();
        }

        return $this->{$this->aggregateIdentifier->getPropertyName()};
    }
}
