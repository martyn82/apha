<?php
declare(strict_types = 1);

namespace Apha\Saga\Storage;

class MemorySagaStorage implements SagaStorage
{
    /**
     * @var array
     */
    private $sagas = [];

    /**
     * @var array
     */
    private $associations = [];

    /**
     */
    public function __construct()
    {
        $this->sagas = [];
        $this->associations = [];
    }

    /**
     * @param string $sagaType
     * @param string $identity
     * @param array $associationValues
     * @param string $data
     * @return void
     */
    public function insert(string $sagaType, string $identity, array $associationValues, string $data)
    {
        $this->sagas[$identity] = [
            'type' => $sagaType,
            'identity' => $identity,
            'associations' => $associationValues,
            'serialized' => $data
        ];

        foreach ($associationValues as $field => $value) {
            if (!array_key_exists($field, $this->associations)) {
                $this->associations[$field] = [];
            }

            if (!array_key_exists($value, $this->associations[$field])) {
                $this->associations[$field][$value] = [];
            }

            if (!in_array($identity, $this->associations[$field][$value])) {
                $this->associations[$field][$value][] = $identity;
            }
        }
    }

    /**
     * @param string $sagaType
     * @param string $identity
     * @param array $associationValues
     * @param string $data
     * @return void
     */
    public function update(string $sagaType, string $identity, array $associationValues, string $data)
    {
        if (!array_key_exists($identity, $this->sagas)) {
            $this->insert($sagaType, $identity, $associationValues, $data);
        } else {
            $this->sagas[$identity]['serialized'] = $data;
        }
    }

    /**
     * @param string $identity
     * @return void
     */
    public function delete(string $identity)
    {
        if (!array_key_exists($identity, $this->sagas)) {
            return;
        }

        foreach ($this->associations as $field => $associations) {
            /* @var $field string */
            /* @var $associations array */

            foreach ($associations as $value => $identities) {
                /* @var $value string */
                /* @var $identities array */

                if (in_array($identity, $identities)) {
                    $this->associations[$field][$value] = array_values(
                        array_filter(
                            $identities,
                            function (string $associatedIdentity) use ($identity) {
                                return $associatedIdentity !== $identity;
                            }
                        )
                    );
                }
            }
        }

        unset($this->sagas[$identity]);
    }

    /**
     * @param string $identity
     * @return string
     */
    public function findById(string $identity): string
    {
        if (!array_key_exists($identity, $this->sagas)) {
            return '';
        }

        return $this->sagas[$identity]['serialized'];
    }

    /**
     * @param string $sagaType
     * @param array $associationValue
     * @return string[]
     */
    public function find(string $sagaType, array $associationValue): array
    {
        $foundIdentities = [];

        foreach ($associationValue as $field => $associatedValue) {
            /* @var $field string */
            /* @var $associatedValue string */

            if (!array_key_exists($field, $this->associations)) {
                return [];
            }

            foreach ($this->associations[$field] as $value => $identities) {
                /* @var $value string */
                /* @var $identities array */

                if ($associatedValue === $value) {
                    $foundIdentities = array_merge($foundIdentities, $identities);
                }
            }
        }

        $foundIdentities = array_unique($foundIdentities);

        return array_reduce(
            $this->sagas,
            function (array $accumulator, array $sagaData) use ($foundIdentities, $sagaType) {
                if (in_array($sagaData['identity'], $foundIdentities) && $sagaData['type'] == $sagaType) {
                    $accumulator[] = $sagaData['identity'];
                }

                return $accumulator;
            },
            []
        );
    }
}