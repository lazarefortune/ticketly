<?php

namespace App\Domain\Event\Service;

use App\Domain\Event\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ReservationCleanupService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function cleanupExpiredReservations(): int
    {
        $reservations = $this->em->getRepository(Reservation::class)->findBy(['status' => 'pending']);

        $count = 0;
        foreach ($reservations as $reservation) {
            if ($reservation->isExpired() && $reservation->isPending()) {
                $event = $reservation->getEvent();
                $event->decrementTakenSpaces($reservation->getQuantity());

                $this->em->remove($reservation);
                $count++;
            }
        }
        $this->em->flush();

        return $count;
    }

    public function clearSessionReservation(SessionInterface $session): void
    {
        $reservationNumber = $session->get('reservation_number');

        if ($reservationNumber) {
            $reservation = $this->em->getRepository(Reservation::class)
                ->findOneBy(['reservationNumber' => $reservationNumber]);

            if ($reservation && $reservation->isPending()) {
                // Libérer les places réservées précédemment
                $event = $reservation->getEvent();
                $event->decrementTakenSpaces($reservation->getQuantity());

                // Supprimer l'ancienne réservation
                $this->em->remove($reservation);
                $this->em->flush();
            }

            // Supprimer la réservation de la session
            $session->remove('reservation_number');
        }
    }
}
