<?php
declare(strict_types = 1);

namespace Apha\Examples\Domain\User;

use Apha\Domain\AggregateRoot;
use Apha\Domain\Identity;

class User extends AggregateRoot
{
    /**
     * @var Identity
     */
    private $id;

    /**
     * @param Identity $id
     * @return User
     */
    public static function create(Identity $id) : self
    {
        $instance = new self($id);
        $instance->applyChange(new UserCreated($id));
        return $instance;
    }

    /**
     * @param Identity $id
     */
    protected function __construct(\Apha\Domain\Identity $id)
    {
        $this->id = $id;
        parent::__construct();
    }

    /**
     * @return Identity
     */
    public function getId() : \Apha\Domain\Identity
    {
        return $this->id;
    }

    /**
     * @param UserCreated $event
     */
    protected function applyUserCreated(UserCreated $event)
    {
        // No changes to the internal state of this aggregate are required
    }
}