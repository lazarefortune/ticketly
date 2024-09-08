<?php

namespace App\Domain\Account\Subscriber;

use App\Domain\Account\Event\AccountDeletedEvent;
use App\Domain\Profile\Event\UserUpdateEvent;
use App\Infrastructure\Mailing\MailService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AccountSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MailService           $mailService,
        private readonly UrlGeneratorInterface $urlGenerator
    )
    {
    }

    public static function getSubscribedEvents() : array
    {
        return [
            AccountDeletedEvent::NAME => 'onAccountDeleted',
            UserUpdateEvent::NAME => 'onUserUpdate',
        ];
    }

    public function onAccountDeleted( AccountDeletedEvent $event ) : void
    {
        $client = $event->getUser();

        $email = $this->mailService->createEmail( 'mails/account/account-deleted.twig', [
            'client' => $client,
            'contact_url' => $this->urlGenerator->generate(
                'app_contact',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        ] )
            ->to( $client->getEmail() )
            ->subject( 'Votre compte a été supprimé' );

        $this->mailService->send( $email );
    }

    public function onUserUpdate( UserUpdateEvent $event ) : void
    {
        $newUser = $event->getNewUser();
        $oldUser = $event->getOldUser();

        $homeUrl = $this->urlGenerator->generate(
            'app_home',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // check if roles have changed if new roles has ROLE_ADMIN send email
        if ( !in_array( 'ROLE_ADMIN', $oldUser->getRoles() ) && in_array( 'ROLE_ADMIN', $newUser->getRoles() ) ) {
            $email = $this->mailService->createEmail( 'mails/account/admin-role-added.twig', [
                'user' => $newUser,
                'home_url' => $homeUrl
            ] )
                ->to( $newUser->getEmail() )
                ->subject( 'Vous avez été promu administrateur' );

            $this->mailService->send( $email );
        }

    }
}