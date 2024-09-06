<?php

namespace App\Http\Admin\Controller;

use App\Domain\Coupon\Entity\Coupon;
use App\Domain\Coupon\Form\CouponForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route( '/coupons', name: 'coupon_' )]
#[IsGranted( 'ROLE_ADMIN' )]
class CouponController extends CrudController
{
    protected string $templatePath = 'coupons';
    protected string $menuItem = 'coupons';
    protected string $searchField = 'code';
    protected string $entity = Coupon::class;
    protected string $routePrefix = 'admin_coupon';
    protected bool $indexOnSave = false;
    protected array $events = [
        'update' => null,
        'delete' => null,
        'create' => null,
    ];

    #[Route( path: '/', name: 'index', methods: ['GET'] )]
    public function index() : Response
    {
        $queryBuilder = $this->getRepository()->createQueryBuilder( 'row' );
        $queryBuilder->orderBy( 'row.expiresAt', 'DESC' );

        return parent::crudIndex( $queryBuilder );
    }

    #[Route( path: '/new', name: 'new', methods: ['POST', 'GET'] )]
    public function new( Request $request ) : Response
    {
        $form = $this->createForm( CouponForm::class );
        $form->handleRequest( $request );

        if ( $form->isSubmitted() && $form->isValid() ) {
            $coupon = $form->getData();
            $this->em->persist( $coupon );
            $this->em->flush();
            $this->addFlash( 'success', 'Créé avec succès' );
            return $this->redirectToRoute( 'admin_coupon_edit', ['id' => $coupon->getId()] );
        }

        return $this->render( 'admin/coupons/new.html.twig', [
            'form' => $form->createView(),
        ] );
    }

    #[Route( path: '/{id<\d+>}', name: 'edit', methods: ['POST', 'GET'] )]
    public function edit(Coupon $coupon, Request $request): Response
    {
        $form = $this->createForm(CouponForm::class, $coupon);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Modifié avec succès');
            return $this->redirectToRoute('admin_coupon_edit', ['id' => $coupon->getId()]);
        }

        return $this->render('admin/coupons/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route( path: '/{id<\d+>}/ajax', name: 'delete_ajax', methods: ['DELETE'] )]
    public function ajaxDelete( Coupon $coupon ): Response
    {
        return $this->crudAjaxDelete( $coupon );
    }

    #[Route( path: '/change-type', name: 'change_type', methods: ['GET'] )]
    public function changeType(Request $request): Response
    {

        $typeCoupon = $request->query->get('typeCoupon');

        $coupon = new Coupon();
        $coupon->setTypeCoupon($typeCoupon);

        $form = $this->createForm( CouponForm::class, $coupon );

        return $this->render( 'admin/coupons/blocs/_coupon_value.html.twig', [
            'form' => $form->createView(),
        ] );
    }
}