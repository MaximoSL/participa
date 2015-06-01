<?php

namespace MXAbierto\Participa\Events;

/**
 * The user has registered event class.
 */
class UserHasRegisteredEvent
{
    /**
     * The user instance.
     *
     * @var MXAbierto\Participa\Models\User
     */
    public $user;

    /**
     * Creates a new user has registered event instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }
}
