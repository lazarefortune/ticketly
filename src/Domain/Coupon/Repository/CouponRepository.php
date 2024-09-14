<?php

namespace App\Domain\Coupon\Repository;

use App\Domain\Coupon\Entity\Coupon;
use App\Infrastructure\Orm\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Coupon>
 */
class CouponRepository extends AbstractRepository
{
    public function __construct( ManagerRegistry $registry )
    {
        parent::__construct( $registry, Coupon::class );
    }

    public function findByEventId( int $eventId ) : array
    {
        return $this->createQueryBuilder( 'c' )
            ->join( 'c.event', 'e' )
            ->where( 'e.id = :eventId' )
            ->setParameter( 'eventId', $eventId )
            ->getQuery()
            ->getResult();
    }
}