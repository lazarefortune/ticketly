<?php

namespace App\Http\Admin\Controller;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Auth\Core\Event\Unverified\AccountVerificationRequestEvent;
use App\Domain\Auth\Event\UserRegistrationCompletedEvent;
use App\Domain\Profile\Event\UserUpdateEvent;
use App\Http\Admin\Data\Crud\UserCrudData;
use App\Http\Admin\Data\Crud\UserEditData;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route( '/utilisateurs', name: 'users_' )]
#[IsGranted( 'ROLE_SUPER_ADMIN' )]
class UserController extends CrudController
{
    protected string $templateDirectory = 'pages/admin';
    protected string $templatePath = 'users';
    protected string $menuItem = 'users';
    protected string $searchField = 'name';
    protected string $entity = User::class;
    protected string $routePrefix = 'admin_users';
    protected bool $indexOnSave = false;
    protected array $events = [
        'update' => UserUpdateEvent::class,
        'delete' => null,
        'create' => UserRegistrationCompletedEvent::class,
    ];

    #[Route( path: '/', name: 'index', methods: ['GET'] )]
    public function index() : Response
    {
        $queryBuilder = $this->getRepository()->createQueryBuilder( 'row' );
        $queryBuilder->where( 'row.roles NOT LIKE :role' )
            ->setParameter( 'role', '%"ROLE_SUPER_ADMIN"%' );

        // remove current user from list
        $user = $this->getUser();
        $queryBuilder->andWhere( 'row.id != :id' )
            ->setParameter( 'id', $user->getId() )
            ->orderBy( 'row.createdAt', 'DESC' )
            ->setMaxResults( 10 );

        return parent::crudIndex( $queryBuilder );
    }

    #[Route( path: '/new', name: 'new', methods: ['POST', 'GET'] )]
    public function new() : Response
    {
        $user = new User();
        $data = new UserCrudData( $user );
        return $this->crudNew( $data );
    }

    #[Route( path: '/{id<\d+>}', name: 'edit', methods: ['POST', 'GET'] )]
    public function edit( User $user ) : Response
    {
        $data = new UserEditData( $user );
        return $this->crudEdit( $data );
    }


    #[Route( path: '/{id<\d+>}/details', name: 'show', methods: ['GET'] )]
    public function show( User $user ) : Response
    {
        $data = new UserCrudData( $user );
        return $this->crudShow( $data );
    }

    #[Route( path: '/{id<\d+>}', methods: ['DELETE'] )]
    public function delete( User $user ) : Response
    {
        return $this->crudDelete( $user );
    }

    #[Route( path: '/{id<\d+>}/ajax', name: 'delete', methods: ['DELETE'] )]
    public function ajaxDelete( User $user ) : Response
    {
        return $this->crudAjaxDelete( $user );
    }

    #[Route( path: '/{id<\d+>}/resend-verification-email', name: 'resend_verification_email', methods: ['GET'] )]
    public function resendVerificationEmail( User $user, EventDispatcherInterface $dispatcher ) : Response
    {
        $this->denyAccessUnlessGranted( 'ROLE_SUPER_ADMIN' );

        $dispatcher->dispatch( new AccountVerificationRequestEvent( $user ) );

        $this->addFlash( 'success', 'Email de vérification envoyé' );

        return $this->redirectToRoute( 'admin_users_edit', ['id' => $user->getId()] );
    }
}
