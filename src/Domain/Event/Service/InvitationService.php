<?php

namespace App\Domain\Event\Service;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\Entity\EventCollaborator;
use App\Domain\Event\Repository\InvitationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class InvitationService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly InvitationRepository $invitationRepository
    ) {}

    public function acceptInvitation(string $token, User $user): void
    {
        $invitation = $this->invitationRepository->findValidInvitation($token);

        if (!$invitation) {
            throw new BadRequestException('Lien d\'invitation invalide ou expiré.');
        }

        if ($user->getEmail() !== $invitation->getEmail()) {
            throw new BadRequestException('Vous ne pouvez pas accepter cette invitation.');
        }

        // Si l'invitation est valide, on l'accepte
        $this->addCollaboratorToEvent($invitation->getEvent(), $user, $invitation->getRoles());

        // Supprimer l'invitation après acceptation
        $this->em->remove($invitation);
        $this->em->flush();
    }

    public function declineInvitation(string $token): void
    {
        $invitation = $this->invitationRepository->findValidInvitation($token);

        if (!$invitation) {
            throw new BadRequestException('Lien d\'invitation invalide ou expiré.');
        }

        // Supprimer l'invitation après refus
        $this->em->remove($invitation);
        $this->em->flush();
    }

    private function addCollaboratorToEvent(Event $event, User $user, array $roles): void
    {
        // Vérifier si l'utilisateur est déjà collaborateur de l'événement
        foreach ($event->getCollaborators() as $collaborator) {
            if ($collaborator->getCollaborator() === $user) {
                // Si déjà collaborateur, on met à jour ses rôles
                $collaborator->setRoles(array_unique(array_merge($collaborator->getRoles(), $roles)));
                return;
            }
        }

        // Sinon, créer un nouveau collaborateur
        $eventCollaborator = new EventCollaborator();
        $eventCollaborator->setEvent($event)
            ->setCollaborator($user)
            ->setRoles($roles);

        $this->em->persist($eventCollaborator);
        $event->addCollaborator($eventCollaborator);
    }
}
