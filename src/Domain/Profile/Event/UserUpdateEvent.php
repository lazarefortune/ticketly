<?php

namespace App\Domain\Profile\Event;

use App\Domain\Auth\Entity\User;

class UserUpdateEvent
{
    public const NAME = 'user.update';

    public function __construct(
        private readonly User $newUser,
        private readonly User $oldUser ) {

    }

    public function getNewUser(): User
    {
        return $this->newUser;
    }

    public function getOldUser(): User
    {
        return $this->oldUser;
    }
}