<?php

namespace App\Domain\Profile\Service;

use App\Domain\Account\Service\AuthService;
use App\Domain\Auth\Entity\User;
use App\Domain\Login\Service\LoginService;
use App\Domain\Profile\Event\Delete\UserDeleteRequestEvent;
use App\Domain\Profile\Event\Delete\UserRequestDeleteSuccessEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DeleteAccountService
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly EntityManagerInterface   $em,
        private readonly LoginService             $loginService,
    )
    {
    }

    public function deleteAccount( User $user ) : void
    {
       # $this->dispatcher->dispatch( new UserRequestDeleteSuccessEvent( $user ) );

        $this->em->remove( $user );
        $this->em->flush();
    }

    public function deleteAccountRequest( User $user, Request $request ) : void
    {
        $this->ensureAccountCanBeDeleted( $user );

        $this->loginService->logout( $request );

        $user->setDeletedAt( new \DateTimeImmutable( sprintf( '+%d days', User::DAYS_BEFORE_DELETION ) ) );
        $this->em->flush();

        $this->dispatcher->dispatch( new UserDeleteRequestEvent( $user ) );
    }

    /**
     * Ensure the user account can be deleted.
     *
     * @param User $user
     * @throws \LogicException If the account deletion is not allowed.
     */
    protected function ensureAccountCanBeDeleted( User $user ) : void
    {
        if ( null !== $user->getDeletedAt() ) {
            throw new \LogicException( sprintf( 'La suppression de ce compte est déjà programmée pour le %s.', $user->getDeletedAt()->format( 'd/m/Y' ) ) );
        }

        $unavailableRolesForDeletion = ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'];
        if ( array_intersect( $unavailableRolesForDeletion, $user->getRoles() ) ) {
            throw new \LogicException( 'Impossible de supprimer ce compte car vous avez un rôle interdisant la suppression.' );
        }
    }


    public function cancelAccountDeletionRequest( User $user ) : void
    {
        $user->setDeletedAt( null );
        $this->em->flush();
    }
}