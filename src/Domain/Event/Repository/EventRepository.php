<?php

namespace App\Domain\Event\Repository;

use App\Domain\Event\Entity\Event;
use App\Infrastructure\Orm\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Event>
 */
class EventRepository extends AbstractRepository
{
    public function __construct( ManagerRegistry $registry )
    {
        parent::__construct( $registry, Event::class );
    }

    public function findNext() : array
    {
        return $this->createQueryBuilder( 'e' )
            ->andWhere( 'e.startDate > :now' )
            ->setParameter( 'now', new \DateTime() )
            ->orderBy( 'e.startDate', 'ASC' )
            ->setMaxResults( 5 )
            ->getQuery()
            ->getResult();
    }
}
