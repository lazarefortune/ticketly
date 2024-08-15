<?php

namespace App\Domain\Event\Repository;

use App\Domain\Event\Entity\Event;
use App\Domain\Event\Entity\Reservation;
use App\Domain\Event\Service\ReservationCleanupService;
use App\Infrastructure\Orm\AbstractRepository;
use App\Infrastructure\Orm\CleanableRepositoryInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Reservation>
 */
class ReservationRepository extends AbstractRepository implements CleanableRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly ReservationCleanupService $reservationCleanupService
    )
    {
        parent::__construct( $registry, Reservation::class );
    }

    public function clean() : int
    {
        return $this->reservationCleanupService->cleanupExpiredReservations();
    }

    public function countReservationsForEvent(Event $event): int
    {
        return $this->count([
            'event' => $event
        ]);
    }

    /**
     * Sum the total amount of reservations for an event
     * @param Event $event
     * @return int
     */
    public function sumReservationsAmountForEvent(Event $event): int
    {
        try {
            $total = $this->createQueryBuilder( 'r' )
                ->select( 'SUM(r.totalAmount)' )
                ->andWhere( 'r.event = :event' )
                ->andWhere( 'r.status = :status' )
                ->setParameter( 'event', $event )
                ->setParameter( 'status', Reservation::STATUS_SUCCESS )
                ->getQuery()
                ->getSingleScalarResult();

            return $total ?? 0;
        } catch ( NoResultException|NonUniqueResultException $e ) {
            return 0;
        }
    }
}