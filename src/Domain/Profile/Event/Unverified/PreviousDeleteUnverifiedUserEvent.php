<?php

namespace App\Domain\Profile\Event\Unverified;

use App\Domain\Auth\Entity\User;

class PreviousDeleteUnverifiedUserEvent
{
    public function __construct(
        private readonly User $user,
    )
    {
    }

    public function getUser() : User
    {
        return $this->user;
    }
}