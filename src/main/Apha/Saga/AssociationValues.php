<?php
declare(strict_types = 1);

namespace Apha\Saga;

class AssociationValues implements \IteratorAggregate
{
    /**
     * @var AssociationValue[]
     */
    private $items;

    /**
     * @param array $associationValues
     */
    public function __construct(array $associationValues)
    {
        $this->items = array_map(
            function (AssociationValue $item): AssociationValue {
                return $item;
            },
            $associationValues
        );
    }

    /**
     * @param AssociationValue $item
     */
    public function add(AssociationValue $item)
    {
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