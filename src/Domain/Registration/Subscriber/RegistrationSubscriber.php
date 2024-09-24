<?php

namespace App\Domain\Registration\Subscriber;

use App\Domain\Auth\Service\AuthMailService;
use App\Domain\Registration\Event\UserCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class RegistrationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AuthMailService $authMailService,
    )
    {
    }

    public static function getSubscribedEvents() : array
    {
        return [
            UserCreatedEvent::NAME => 'onUserRegistered',
        ];
    }

    /**
     * Send welcome email and verification email to the user
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function onUserRegistered( UserCreatedEvent $event ) : void
    {
        $user = $event->getUser();

        $this->authMailService->sendWelcomeEmail( $user );
        $this->authMailService->sendVerificationEmail( $user );
    }
}