<?php

namespace App\Domain\History\Service;

use App\Domain\Auth\Entity\User;
use App\Domain\Course\Entity\Formation;
use App\Domain\History\Entity\Progress;
use App\Domain\History\Repository\ProgressRepository;
use Symfony\Component\Security\Core\User\UserInterface;

class HistoryService
{
    public function __construct( private readonly ProgressRepository $progressRepository )
    {
    }

    /**
     * @return Progress[]
     */
    public function getLastWatchedContent( User $user ) : array
    {
        return $this->progressRepository->findLastForUser( $user );
    }

    public function getNextContentIdToWatch( User $user, Formation $formation ) : ?int
    {
        $ids = $formation->getModulesIds();
        $finishedIds = $this->progressRepository->findFinishedIdWithin( $user, $ids );
        foreach ( $ids as $id ) {
            if ( !in_array( $id, $finishedIds ) ) {
                return $id;
            }
        }

        return null;
    }
}