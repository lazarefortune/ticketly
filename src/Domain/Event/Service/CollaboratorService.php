<?php

namespace App\Domain\Event\Service;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\Entity\EventCollaborator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class CollaboratorService
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {}

    /**
     * Modifie les rôles d'un collaborateur pour un événement.
     *
     * @param Event $event
     * @param User $collaborator
     * @param array $roles
     * @return void
     * @throws BadRequestException
     */
    public function modifyRoles(Event $event, User $collaborator, array $roles): void
    {
        // Récupérer le collaborateur existant pour cet événement
        $eventCollaborator = $this->findCollaborator($event, $collaborator);

        if (!$eventCollaborator) {
            throw new BadRequestException('Le collaborateur n\'existe pas pour cet événement.');
        }

        // Mettre à jour les rôles du collaborateur
        $eventCollaborator->setRoles($roles);
        $this->em->flush();
    }

    /**
     * Retire un collaborateur de l'événement.
     *
     * @param Event $event
     * @param User $collaborator
     * @return void
     * @throws BadRequestException
     */
    public function removeCollaborator(Event $event, User $collaborator): void
    {
        // Récupérer le collaborateur existant pour cet événement
        $eventCollaborator = $this->findCollaborator($event, $collaborator);

        if (!$eventCollaborator) {
            throw new BadRequestException('Le collaborateur n\'existe pas pour cet événement.');
        }

        // Supprimer le collaborateur de l'événement
        $this->em->remove($eventCollaborator);
        $this->em->flush();
    }

    /**
     * Trouve un collaborateur pour un événement.
     *
     * @param Event $event
     * @param User $collaborator
     * @return EventCollaborator|null
     */
    public function findCollaborator( Event $event, User $collaborator): ?EventCollaborator
    {
        foreach ($event->getCollaborators() as $eventCollaborator) {
            if ($eventCollaborator->getCollaborator() === $collaborator) {
                return $eventCollaborator;
            }
        }

        return null;
    }
}
