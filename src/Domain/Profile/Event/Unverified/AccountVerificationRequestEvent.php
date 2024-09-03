<?php

namespace App\Domain\Profile\Event\Unverified;

use App\Domain\Auth\Entity\User;

class AccountVerificationRequestEvent
{
    public const NAME = 'account.verification.request';

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