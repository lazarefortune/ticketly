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

    /**
     * Return the next events online to come
     * @return array<Event>
     */
    public function findNext( int $limit = 0 ) : array
    {
        $query = $this->createQueryBuilder( 'e' )
            ->andWhere( 'e.startDate > :now' )
            ->andWhere( 'e.isActive = true' )
            ->setParameter( 'now', new \DateTime() )
            ->orderBy( 'e.startDate', 'ASC' );

        if( $limit > 0 ) {
            $query->setMaxResults( $limit );
        }

        return $query->getQuery()->getResult();
    }
}
