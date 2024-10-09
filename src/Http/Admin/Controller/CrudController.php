<?php

namespace App\Http\Admin\Controller;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Prestation\Entity\Prestation;
use App\Helper\Paginator\PaginatorInterface;
use App\Http\Admin\Data\CrudDataInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * @template E
 *
 * @method \App\Domain\Auth\Core\Entity\User getUser()
 */
abstract class CrudController extends BaseController
{
    /**
     * @var class-string<E>
     */
    protected string $entity = User::class;
    protected string $templateDirectory = 'admin';
    protected string $templatePath = 'users';
    protected string $menuItem = '';
    protected string $routePrefix = '';
    protected string $searchField = 'name';
    protected bool $indexOnSave = true;
    protected array $events = [
        'update' => null,
        'delete' => null,
        'create' => null,
    ];

    public function __construct(
        protected EntityManagerInterface          $em,
        protected PaginatorInterface              $paginator,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly RequestStack             $requestStack
    )
    {
    }

    public function crudIndex( QueryBuilder $query = null, array $extraParams = [] ) : Response
    {
        /** @var Request $request */
        $request = $this->requestStack->getCurrentRequest();
        $query = $query ? : $this->getRepository()
            ->createQueryBuilder( 'row' )
            ->orderBy( 'row.createdAt', 'DESC' );
        if ( $request->get( 'q' ) ) {
            $query = $this->applySearch( trim( (string)$request->get( 'q' ) ), $query );
        }
        $this->paginator->allowSort( 'row.id', 'row.title' );
        $rows = $this->paginator->paginate( $query->getQuery() );

        $template = "{$this->templateDirectory}/{$this->templatePath}/index.html.twig";
        return $this->render( $template, [
            'rows' => $rows,
            'searchable' => true,
            'menu' => $this->menuItem,
            'prefix' => $this->routePrefix,
            ...$extraParams,
        ] );
    }

    public function crudEdit( CrudDataInterface $data ) : Response
    {
        /** @var Request $request */
        $request = $this->requestStack->getCurrentRequest();
        $form = $this->createForm( $data->getFormClass(), $data );
        $form->handleRequest( $request );

        if ( $form->isSubmitted() && $form->isValid() ) {
            /** @var E $entity */
            $entity = $data->getEntity();
            $old = clone $entity;
            $data->hydrate();
            $this->em->flush();
            if ( $this->events['update'] ?? null ) {
                $this->dispatcher->dispatch( new $this->events['update']( $entity, $old ), $this->events['update']::NAME );
            }
            $this->addFlash( 'success', 'L\'item a bien été modifié' );

            return $this->redirectAfterSave( $entity );
        }

        $template = "{$this->templateDirectory}/{$this->templatePath}/edit.html.twig";
        return $this->render( $template, [
            'form' => $form->createView(),
            'entity' => $data->getEntity(),
            'menu' => $this->menuItem,
        ] );
    }

    public function crudNew( CrudDataInterface $data ) : Response
    {
        /** @var Request $request */
        $request = $this->requestStack->getCurrentRequest();
        $form = $this->createForm( $data->getFormClass(), $data );
        $form->handleRequest( $request );
        if ( $form->isSubmitted() && $form->isValid() ) {
            /** @var E $entity */
            $entity = $data->getEntity();
            $data->hydrate();
            $this->em->persist( $entity );
            $this->em->flush();
            if ( $this->events['create'] ?? null ) {
                $this->dispatcher->dispatch( new $this->events['create']( $data->getEntity() ), $this->events['create']::NAME );
            }
            $this->addFlash( 'success', 'L\'item a bien été créé' );

            return $this->redirectAfterSave( $entity );
        }

        $template = "{$this->templateDirectory}/{$this->templatePath}/new.html.twig";
        return $this->render( $template, [
            'form' => $form->createView(),
            'entity' => $data->getEntity(),
            'menu' => $this->menuItem,
        ] );
    }

    public function crudDelete( object $entity, ?string $redirectRoute = null ) : RedirectResponse
    {
        $this->em->remove( $entity );
        if ( $this->events['delete'] ?? null ) {
            $this->dispatcher->dispatch( new $this->events['delete']( $entity ), $this->events['delete']::NAME );
        }
        $this->em->flush();
        $this->addFlash( 'success', 'Le contenu a bien été supprimé' );

        return $this->redirectToRoute( $redirectRoute ? : ( $this->routePrefix . '_index' ) );
    }

    public function crudAjaxDelete( object $entity ) : JsonResponse
    {
        $this->em->remove( $entity );
        if ( $this->events['delete'] ?? null ) {
            $this->dispatcher->dispatch( new $this->events['delete']( $entity ), $this->events['delete']::NAME );
        }
        $this->em->flush();

        return $this->json([
            'success' => true,
        ]);
    }

    public function crudShow( object $entity, array $extraParams = [] ) : Response
    {
        $template = "{$this->templateDirectory}/{$this->templatePath}/show.html.twig";
        return $this->render( $template, [
            'entity' => $entity,
            'menu' => $this->menuItem,
            ...$extraParams,
        ] );
    }

    public function getRepository() : EntityRepository
    {
        /* @var EntityRepository */
        return $this->em->getRepository( $this->entity );
        /* @phpstan-ignore-line */
    }

    protected function applySearch( string $search, QueryBuilder $query ) : QueryBuilder
    {
        return $query
            ->where( "LOWER(row.{$this->searchField}) LIKE :search" )
            ->setParameter( 'search', '%' . strtolower( $search ) . '%' );
    }

    /**
     * @param E $entity
     */
    protected function redirectAfterSave( $entity ) : RedirectResponse
    {
        if ( $this->indexOnSave ) {
            return $this->redirectToRoute( $this->routePrefix . '_index' );
        }

        return $this->redirectToRoute( $this->routePrefix . '_edit', ['id' => $entity->getId()] );
    }
}
