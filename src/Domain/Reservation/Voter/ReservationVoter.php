<?php

namespace App\Domain\Reservation\Voter;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\Entity\EventCollaborator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ReservationVoter extends Voter
{

    public const EDIT = 'RESERVATION_EDIT';
    public const DELETE = 'RESERVATION_DELETE';
    public const VIEW = 'RESERVATION_VIEW';

    public function __construct(
        private readonly Security $security
    )
    {
    }

    protected function supports( string $attribute, mixed $subject ) : bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE, self::VIEW])
            && $subject instanceof Event;
    }

    protected function voteOnAttribute( string $attribute, mixed $subject, TokenInterface $token ) : bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Event $event */
        $event = $subject;

        return match ( $attribute ) {
            self::EDIT => $this->isOrganizer( $event, $user ) || $this->hasRole( $event, $user, EventCollaborator::ROLE_RESERVATIONS ),
            self::DELETE => $this->canDelete( $event, $user ),
            self::VIEW => $this->isOrganizerOrCollaborator( $event, $user ) || $this->security->isGranted( 'ROLE_ADMIN' ),
            default => false,
        };
    }


    private function isOrganizerOrCollaborator( Event $event, User $user ) : bool
    {
        return $this->isOrganizer($event, $user) || $this->isCollaborator( $event, $user );
    }

    private function isCollaborator( Event $event, User $user ) : bool
    {
        return $event->getCollaborators()->exists(
            fn( $key, $collaborator ) => $collaborator->getCollaborator() === $user
        );
    }

    private function canDelete( Event $event, User $user ) : bool
    {
        return false;
    }

    private function hasRole( Event $event, User $user, string $role ) : bool
    {
        return $event->getCollaborators()->exists(
            fn( $key, $collaborator ) => $collaborator->getCollaborator() === $user && $collaborator->hasRole( $role )
        );
    }

    private function isOrganizer( Event $event, User $user ) : bool
    {
        return $event->getOrganizer() === $user;
    }

}