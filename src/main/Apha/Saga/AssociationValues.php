<?php
declare(strict_types = 1);

namespace Apha\Saga;

use JMS\Serializer\Annotation as Serializer;

class AssociationValues implements \IteratorAggregate
{
    /**
     * @Serializer\Type("array<Apha\Saga\AssociationValue>")
     * @var AssociationValue[]
     */
    private $items = [];

    /**
     * @param array $associationValues
     */
    public function __construct(array $associationValues)
    {
        array_walk(
            $associationValues,
            function (AssociationValue $item) {
                $this->add($item);
            }
        );
    }

    /**
     * @param AssociationValue $item
     */
    public function add(AssociationValue $item)
    {
        if ($this->contains($item)) {
            return;
        }

        $this->items[] = $item;
    }

    /**
     * @return int
     */
    public function size(): int
    {
        return count($this->items);
    }

    /**
     */
    public function clear()
    {
        $this->items = [];
    }

    /**
     * @param AssociationValue $associationValue
     * @return bool
     */
    public function contains(AssociationValue $associationValue): bool
    {
        /* @var $item AssociationValue */
        foreach ($this->items as $item) {
            if ($item->getKey() === $associationValue->getKey()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return \Iterator
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * @return array
     */
    public function getArrayCopy(): array
    {
        return $this->getIterator()->getArrayCopy();
    }
}