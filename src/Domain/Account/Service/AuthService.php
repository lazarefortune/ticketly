<?php

namespace App\Domain\Account\Service;

use App\Domain\Auth\Dto\NewUserData;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Event\EmailConfirmationCompletedEvent;
use App\Domain\Auth\Event\EmailConfirmationRequestedEvent;
use App\Domain\Auth\Event\UserRegistrationCompletedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class AuthService
{
    public function __construct(
        private readonly EventDispatcherInterface    $eventDispatcher,
    )
    {
    }

    /**
     * Send an email to the user to confirm his account
     * @param User $user
     * @return void
     */
    public function sendAccountConfirmationEmail( User $user ) : void
    {
        $emailConfirmationRequestedEvent = new EmailConfirmationRequestedEvent( $user );
        $this->eventDispatcher->dispatch( $emailConfirmationRequestedEvent, EmailConfirmationRequestedEvent::NAME );
    }

    public function logout( Request $request = null ) : void
    {
        $request = $request ?: new Request();
        $request->getSession()->invalidate();
    }
}