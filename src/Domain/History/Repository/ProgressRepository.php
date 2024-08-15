<?php

namespace App\Domain\History\Repository;

use App\Domain\Application\Entity\Content;
use App\Domain\Auth\Entity\User;
use App\Domain\Course\Entity\Course;
use App\Domain\Course\Entity\Formation;
use App\Domain\History\Entity\Progress;
use App\Infrastructure\Orm\AbstractRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends AbstractRepository<Progress>
 */
class ProgressRepository extends AbstractRepository
{
    public function __construct( ManagerRegistry $registry )
    {
        parent::__construct( $registry, Progress::class );
    }

    public function queryAllForUser( User $user ) : QueryBuilder
    {
        return $this->createQueryBuilder( 'p' )
            ->orderBy( 'p.updatedAt', 'DESC' )
            ->leftJoin( 'p.content', 'c' )
            ->addSelect( 'c' )
            ->where( '(c INSTANCE OF ' . Course::class . ' OR c INSTANCE OF ' . Formation::class . ')' )
            ->andWhere( 'p.author = :user' )
            ->setParameter( 'user', $user->getId() );
    }

    public function findOneByContent( User $user, Content $content ) : ?Progress
    {
        return $this->findOneBy( [
            'content' => $content,
            'author' => $user,
        ] );
    }

    public function findLastForUser( User $user ) : array
    {
        return $this->createQueryBuilder( 'p' )
            ->leftJoin( 'p.content', 'c' )
            ->addSelect( 'c' )
            ->where( 'p.author = :user' )
            ->andWhere( '(c INSTANCE OF ' . Course::class . ' OR c INSTANCE OF ' . Formation::class . ')' )
            ->andWhere( 'p.progress < :progress' )
            ->orderBy( 'p.updatedAt', 'DESC' )
            ->setMaxResults( 4 )
            ->setParameters( [
                'user' => $user,
                'progress' => Progress::TOTAL,
            ] )
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Content[] $contents
     *
     * @return Progress[]
     */
    public function findForContents( User $user, array $contents ) : array
    {
        return $this->createQueryBuilder( 'p' )
            ->leftJoin( 'p.content', 'c' )
            ->addSelect( 'partial c.{id}' )
            ->where( 'p.content IN (:ids)' )
            ->andWhere( 'p.author = :user' )
            ->setParameters( [
                'ids' => array_map( fn ( Content $c ) => $c->getId(), $contents ),
                'user' => $user,
            ] )
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les ids lu parmis la liste passée en paramètre.
     *
     * @return int[]
     */
    public function findFinishedIdWithin( User $user, array $ids ) : array
    {
        return array_map( fn ( Progress $p ) => $p->getContent()->getId() ? : 0, $this->createQueryBuilder( 'p' )
            ->where( 'p.content IN (:ids)' )
            ->andWhere( 'p.author = :user' )
            ->andWhere( 'p.progress = :total' )
            ->setParameters( [
                'user' => $user,
                'ids' => $ids,
                'total' => Progress::TOTAL,
            ] )
            ->getQuery()
            ->getResult() );
    }
}