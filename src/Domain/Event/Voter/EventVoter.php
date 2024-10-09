<?php

namespace App\Domain\Event\Voter;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\Entity\EventCollaborator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class EventVoter extends Voter
{

    public const CREATE = 'EVENT_CREATE';
    public const EDIT = 'EVENT_EDIT';
    public const DELETE = 'EVENT_DELETE';
    public const VIEW = 'EVENT_VIEW';
    public const INVITE_COLLABORATOR = 'EVENT_INVITE_COLLABORATOR';

    public function __construct(
        private readonly Security $security
    )
    {
    }

    protected function supports( string $attribute, mixed $subject ) : bool
    {
        return in_array($attribute, [self::CREATE, self::EDIT, self::DELETE, self::VIEW, self::INVITE_COLLABORATOR])
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
            self::CREATE => $this->canCreate( $user ),
            self::EDIT => $this->isOrganizer( $event, $user ) || $this->hasRoles( $event, $user ,EventCollaborator::ROLE_MANAGE_EVENT ),
            self::DELETE => $this->canDelete( $event, $user ),
            self::VIEW => $this->isOrganizerOrCollaborator( $event, $user ) || $this->security->isGranted( 'ROLE_ADMIN' ),
            self::INVITE_COLLABORATOR => $this->hasRoles( $event, $user , EventCollaborator::ROLE_MANAGER ) || $this->isOrganizer( $event, $user ),
            default => false,
        };

    }

    private function canCreate( User $user ) : bool
    {
        return $user->getStripeAccountId() !== null;
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

    private function canDelete( Event $event, UserInterface $user ) : bool
    {
        $isOrganizer = $event->getOrganizer() === $user;
        $canDelete = $event->getTickets()->isEmpty() && $event->getEndDate() > new \DateTime();

        return $isOrganizer && $canDelete;
    }

    private function hasRoles( Event $event, User $user , string $role ) : bool
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