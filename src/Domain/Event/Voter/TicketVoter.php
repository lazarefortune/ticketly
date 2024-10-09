<?php

namespace App\Domain\Event\Voter;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\Entity\EventCollaborator;
use App\Domain\Event\Entity\Ticket;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TicketVoter extends Voter
{
    public const VALIDATE = 'TICKET_VALIDATE';
    public const VIEW = 'TICKET_VIEW';
    public const EDIT = 'TICKET_EDIT';
    public const DELETE = 'TICKET_DELETE';

    public function __construct(
        private readonly Security $security
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VALIDATE, self::VIEW, self::EDIT, self::DELETE])
            && ($subject instanceof Ticket || $subject instanceof Event);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        // Si le sujet est un Ticket, récupérons l'événement associé
        $event = $subject instanceof Ticket ? $subject->getEvent() : $subject;

        return match ($attribute) {
            self::VALIDATE => $this->canValidate($event, $user),
            self::VIEW => $this->canView($event, $user),
            self::EDIT => $this->canEdit($event, $user),
            self::DELETE => $this->canDelete($event, $user),
            default => false,
        };
    }

    private function canValidate(Event $event, User $user): bool
    {
        // L'organisateur ou un collaborateur avec le rôle ROLE_TICKETS peut créer des tickets
        return $this->isOrganizer($event, $user) || $this->hasRole($event, $user, EventCollaborator::ROLE_TICKETS);
    }

    private function canView(Event $event, User $user): bool
    {
        // L'organisateur ou un collaborateur avec le rôle ROLE_TICKETS peut voir les tickets
        return $this->isOrganizer($event, $user) || $this->hasRole($event, $user, EventCollaborator::ROLE_TICKETS);
    }

    private function canEdit(Event $event, User $user): bool
    {
        // L'organisateur ou un collaborateur avec le rôle ROLE_TICKETS peut éditer les tickets
        return $this->isOrganizer($event, $user) || $this->hasRole($event, $user, EventCollaborator::ROLE_TICKETS);
    }

    private function canDelete(Event $event, User $user): bool
    {
        // L'organisateur ou un collaborateur avec le rôle ROLE_TICKETS peut supprimer les tickets
        return $this->isOrganizer($event, $user) || $this->hasRole($event, $user, EventCollaborator::ROLE_TICKETS);
    }

    private function isOrganizer(Event $event, User $user): bool
    {
        return $event->getOrganizer() === $user;
    }

    private function hasRole(Event $event, User $user, string $role): bool
    {
        return $event->getCollaborators()->exists(
            fn($key, EventCollaborator $collaborator) => $collaborator->getCollaborator() === $user && $collaborator->hasRole($role)
        );
    }
}
