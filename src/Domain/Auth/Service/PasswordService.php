<?php

namespace App\Domain\Auth\Service;

use App\Domain\Auth\Entity\PasswordReset;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Event\ResetPasswordRequestedEvent;
use App\Domain\Auth\Repository\PasswordResetRepository;
use App\Domain\Profile\Event\PasswordChangeSuccessEvent;
use App\Domain\Profile\Exception\TooManyPasswordResetRequestException;
use App\Infrastructure\Security\TokenGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PasswordService
{
    public function __construct(
        private readonly EntityManagerInterface      $entityManager,
        private readonly TokenGeneratorService       $tokenGeneratorService,
        private readonly EventDispatcherInterface    $eventDispatcher,
        private readonly PasswordResetRepository     $passwordResetRepository,
        private readonly UserPasswordHasherInterface $userPasswordHasher
    )
    {
    }

    /**
     * Create a password reset request and dispatch an event to send an email
     * @param string $email
     * @return void
     * @throws TooManyPasswordResetRequestException
     */
    public function forgotPasswordRequest( string $email ) : void
    {
        $user = $this->entityManager->getRepository( User::class )->findOneBy( ['email' => $email] );

        if ( $user ) {
            $lastPasswordReset = $this->passwordResetRepository->findLatestValidPasswordReset( $user );
            if ( $lastPasswordReset && ( !$lastPasswordReset->isExpired() ) ) {
                throw new TooManyPasswordResetRequestException( $lastPasswordReset );
            } else {
                if ( $lastPasswordReset ) {
                    $this->entityManager->remove( $lastPasswordReset );
                }
            }

            $passwordReset = ( new PasswordReset() )
                ->setAuthor( $user )
                ->setToken( $this->tokenGeneratorService->generate() )
                ->setCreatedAt( new \DateTimeImmutable() );

            $this->entityManager->persist( $passwordReset );
            $this->entityManager->flush();

            $resetPasswordEvent = new ResetPasswordRequestedEvent( $passwordReset );
            $this->eventDispatcher->dispatch( $resetPasswordEvent, ResetPasswordRequestedEvent::NAME );
        }
    }

    /**
     * Get the user associated with a password reset token
     * @param string $token
     * @return User|null
     */
    public function getUserByPasswordResetToken( string $token ) : ?User
    {
        $passwordReset = $this->passwordResetRepository->findOneBy( ['token' => $token] );
        if ( $passwordReset && !$passwordReset->isExpired() ) {
            return $passwordReset->getAuthor();
        }
        return null;
    }

    /**
     * Reset the password of a user
     * @param User $user
     * @param string $newPassword
     * @return void
     */
    public function resetPassword( User $user, string $newPassword ) : void
    {
        $user->setPassword( $this->userPasswordHasher->hashPassword( $user, $newPassword ) );
        $this->entityManager->flush();

        // remove password reset request
        $passwordReset = $this->passwordResetRepository->findOneBy( ['author' => $user] );
        if ( $passwordReset ) {
            $this->entityManager->remove( $passwordReset );
            $this->entityManager->flush();
        }

        $passwordUpdatedEvent = new PasswordChangeSuccessEvent( $user );
        $this->eventDispatcher->dispatch( $passwordUpdatedEvent );
    }
}