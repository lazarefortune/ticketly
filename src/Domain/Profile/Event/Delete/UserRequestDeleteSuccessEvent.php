<?php

namespace App\Domain\Profile\Event\Delete;

use App\Domain\Auth\Entity\User;

class UserRequestDeleteSuccessEvent
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