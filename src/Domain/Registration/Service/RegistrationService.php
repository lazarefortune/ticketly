<?php

namespace App\Domain\Registration\Service;

use App\Domain\Auth\Dto\NewUserData;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Repository\UserRepository;
use App\Domain\Registration\Event\UserCreatedEvent;
use App\Domain\Registration\Event\UserValidatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class RegistrationService
{
    public function __construct(
        private readonly EntityManagerInterface      $entityManager,
        private readonly EventDispatcherInterface    $eventDispatcher,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly UserRepository              $userRepository,
        private readonly VerifyEmailHelperInterface  $verifyEmailHelper,
    )
    {
    }

    public function createUser( NewUserData $userDto ) : User
    {
        $user = $userDto->user
            ->setEmail( $userDto->email )
            ->setFullname( $userDto->fullname )
            ->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $userDto->user,
                    $userDto->plainPassword
                )
            )
            ->setCgu( true );
        $this->entityManager->persist( $user );
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch( new UserCreatedEvent( $user ), UserCreatedEvent::NAME );

        return $user;
    }

    /**
     * Validate the email confirmation link, and activate the user account
     * @param mixed $userId
     * @param string $uri
     * @throws VerifyEmailExceptionInterface
     * @throws Exception
     */
    public function validateUser( mixed $userId, string $uri ) : void
    {
        if ( !$userId || !( $user = $this->userRepository->find( $userId ) ) )
        {
            throw new Exception( "L'utilisateur n'existe pas." );
        }

        if ( $user->isVerified() )
        {
            throw new Exception( "L'utilisateur a déjà été vérifié." );
        }

        $this->verifyEmailHelper->validateEmailConfirmation( $uri, $user->getId(), $user->getEmail() );

        $user->setIsVerified( true );
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch( new UserValidatedEvent( $user ), UserValidatedEvent::NAME );
    }
}