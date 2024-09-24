<?php

namespace App\Domain\Registration\Event;

use App\Domain\Auth\Entity\User;

class UserValidatedEvent
{
    public const NAME = 'user.validated';

    public function __construct(
        public readonly User $user,
    )
    {
    }

    public function getUser() : User
    {
        return $this->user;
    }
}