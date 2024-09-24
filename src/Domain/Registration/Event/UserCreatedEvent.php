<?php

namespace App\Domain\Registration\Event;

use App\Domain\Auth\Entity\User;

class UserCreatedEvent
{

    const NAME = 'user.created';

    public function __construct(
        private readonly User $user
    )
    {
    }

    public function getUser() : User
    {
        return $this->user;
    }
}