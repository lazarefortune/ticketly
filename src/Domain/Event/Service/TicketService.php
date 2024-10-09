<?php

namespace App\Domain\Event\Service;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Event\Entity\EventCollaborator;
use App\Domain\Event\Entity\Ticket;
use App\Domain\Event\Repository\TicketRepository;

class TicketService
{
    public function __construct(
        private readonly TicketRepository $ticketRepository,
    )
    {
    }

    public function getTicketForUser(string $ticketNumber, User $user): ?Ticket
    {
        // Récupérer le ticket en fonction du numéro fourni
        $ticket = $this->ticketRepository->findOneBy(['ticketNumber' => $ticketNumber]);

        if (!$ticket) {
            // Le ticket n'existe pas
            return null;
        }

        $event = $ticket->getEvent();

        // Vérifier si l'utilisateur est l'organisateur de l'événement
        if ($event->getOrganizer() === $user) {
            return $ticket;
        }

        // Vérifier si l'utilisateur est un collaborateur avec le rôle approprié
        if ($this->userHasRole($event, $user, EventCollaborator::ROLE_TICKETS)) {
            return $ticket;
        }

        // L'utilisateur n'est pas autorisé à accéder au ticket
        return null;
    }

    private function userHasRole($event, User $user, string $role): bool
    {
        foreach ($event->getCollaborators() as $collaborator) {
            if ($collaborator->getCollaborator() === $user && $collaborator->hasRole($role)) {
                return true;
            }
        }
        return false;
    }
}
