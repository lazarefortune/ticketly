<?php

namespace App\Domain\Event\Repository;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Event\Entity\Event;
use App\Infrastructure\Orm\AbstractRepository;
use Doctrine\ORM\QueryBuilder;
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
            ->andWhere( 'e.endSaleDate > :now' )
            ->andWhere( 'e.isActive = true' )
            ->setParameter( 'now', new \DateTime() )
            ->orderBy( 'e.startDate', 'ASC' );

        if( $limit > 0 ) {
            $query->setMaxResults( $limit );
        }

        return $query->getQuery()->getResult();
    }

    /**
     * Return all events where the user is the organizer or collaborator
     * @return array<Event>
     */
    public function findEventsByUser( User $user ) : array
    {
        $query = $this->createQueryBuilder( 'e' )
            ->leftJoin( 'e.collaborators', 'c' )
            ->where( 'e.organizer = :user OR c.collaborator = :user' )
            ->setParameter( 'user', $user )
            ->orderBy( 'e.startDate', 'ASC' );

        return $query->getQuery()->getResult();
    }

    /**
     * Return all events where the user is the organizer or collaborator
     * @param User $user
     * @return QueryBuilder
     */
    public function getQueryEventsByUser( User $user ) : \Doctrine\ORM\QueryBuilder
    {
        return $this->createQueryBuilder( 'e' )
            ->leftJoin( 'e.collaborators', 'c' )
            ->where( 'e.organizer = :user OR c.collaborator = :user' )
            ->setParameter( 'user', $user )
            ->orderBy( 'e.startDate', 'ASC' );
    }
}
