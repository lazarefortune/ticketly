<?php

namespace App\Domain\Profile\Subscriber\Delete;

use App\Domain\Profile\Event\Delete\PreviousUserDeleteRequestEvent;
use App\Domain\Profile\Event\Delete\UserDeleteRequestEvent;
use App\Domain\Profile\Event\Delete\UserRequestDeleteSuccessEvent;
use App\Infrastructure\Mailing\MailService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserDeleteRequestSubscriber implements EventSubscriberInterface
{
    public function __construct( private readonly MailService $mailService )
    {
    }

    public static function getSubscribedEvents() : array
    {
        return [
            UserDeleteRequestEvent::class => 'onUserDeleteRequest',
            PreviousUserDeleteRequestEvent::class => 'onPreviousUserDeleteRequest',
            UserRequestDeleteSuccessEvent::class => 'onUserRequestDeleteSuccess',
        ];
    }

    public function onPreviousUserDeleteRequest( PreviousUserDeleteRequestEvent $event ) : void
    {
        $user = $event->getUser();
        $email = $this->mailService->createEmail( 'mails/account/delete/previous-request.twig', [
            'user' => $user,
        ] )
            ->to( $user->getEmail() )
            ->subject( 'Votre compte va bientôt être supprimé' );

        $this->mailService->send( $email );

    }

    public function onUserDeleteRequest( UserDeleteRequestEvent $event ) : void
    {
        $user = $event->getUser();
        $email = $this->mailService->createEmail( 'mails/account/delete/request.twig', [
            'user' => $user,
        ] )
            ->to( $user->getEmail() )
            ->subject( 'Demande de suppression de votre compte' );

        $this->mailService->send( $email );
    }

    public function onUserRequestDeleteSuccess( UserRequestDeleteSuccessEvent $event ) : void
    {
        $user = $event->getUser();
        $email = $this->mailService->createEmail( 'mails/account/delete/deleted-success.twig', [
            'user' => $user,
        ] )
            ->to( $user->getEmail() )
            ->subject( 'Votre compte a été supprimé' );

        $this->mailService->send( $email );
    }

}