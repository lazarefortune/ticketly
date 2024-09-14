<?php

namespace App\Domain\Event\Repository;

use App\Domain\Event\Entity\Event;
use App\Domain\Event\Entity\Invitation;
use App\Infrastructure\Orm\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Invitation>
 */
class InvitationRepository extends AbstractRepository
{
    public function __construct( ManagerRegistry $registry )
    {
        parent::__construct( $registry, Invitation::class );
    }

    public function findValidInvitation( string $token ) : ?Invitation
    {
        return $this->createQueryBuilder( 'i' )
            ->where( 'i.token = :token' )
            ->andWhere( 'i.status = :status' )
            ->andWhere( 'i.expiryDate > :now' )
            ->setParameters( [
                'token' => $token,
                'status' => Invitation::STATUS_PENDING,
                'now' => new \DateTime(),
            ] )
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByEvent( Event $event )
    {
        return $this->createQueryBuilder( 'i' )
            ->where( 'i.event = :event' )
            ->setParameter( 'event', $event )
            ->getQuery()
            ->getResult();
    }
}
