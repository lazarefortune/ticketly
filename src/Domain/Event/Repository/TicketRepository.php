<?php

namespace App\Domain\Event\Repository;

use App\Domain\Event\Entity\Event;
use App\Domain\Event\Entity\Ticket;
use App\Infrastructure\Orm\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends AbstractRepository<Ticket>
 */
class TicketRepository extends AbstractRepository
{
    public function __construct( ManagerRegistry $registry )
    {
        parent::__construct( $registry, Ticket::class );
    }

    public function getEventTicketsQueryBuilder( $event ): QueryBuilder
    {
        return $this->createQueryBuilder( 't' )
            ->andWhere( 't.event = :event' )
            ->setParameter( 'event', $event );
    }

    /**
     * @param string $email
     * @param Event $event
     * @return Ticket|null
     */
    public function findTicketByEmailAndEvent(string $email, Event $event): ?Ticket
    {
        return $this->findOneBy([
            'email' => $email,
            'event' => $event
        ]);
    }

    /**
     * Count the number of tickets for an event
     * @param Event $event
     * @return int
     */
    public function countTicketsForEvent(Event $event): int
    {
        return $this->count([
            'event' => $event
        ]);
    }

}
