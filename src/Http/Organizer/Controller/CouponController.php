<?php

namespace App\Http\Organizer\Controller;

use App\Domain\Coupon\Entity\Coupon;
use App\Domain\Coupon\Form\CouponForm;
use App\Domain\Coupon\Voter\CouponVoter;
use App\Domain\Event\Entity\Event;
use App\Http\Admin\Controller\CrudController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route( '/coupons', name: 'event_coupon_' )]
#[IsGranted( 'ROLE_USER' )]
class CouponController extends CrudController
{
    protected string $templateDirectory = 'organizer';
    protected string $templatePath = 'coupons';
    protected string $menuItem = 'coupons';
    protected string $searchField = 'code';
    protected string $entity = Coupon::class;
    protected string $routePrefix = 'organizer_event_coupon';
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

    #[Route(path: '/new/{event}', name: 'new', methods: ['POST', 'GET'])]
    public function new(Request $request, Event $event): Response
    {
        // Utilisation du Voter pour limiter l'accès
        $this->denyAccessUnlessGranted(CouponVoter::COUPON_MANAGE, $event);

        $coupon = new Coupon();
        $coupon->setEvent($event);

        $form = $this->createForm(CouponForm::class, $coupon);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($coupon);
            $this->em->flush();
            $this->addFlash('success', 'Coupon créé avec succès');
            return $this->redirectToRoute('organizer_event_coupons', ['id' => $event->getId()]);
        }

        return $this->render('pages/organizer/coupons/new.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    #[Route(path: '/edit/{id<\d+>}', name: 'edit', methods: ['POST', 'GET'])]
    public function edit(Coupon $coupon, Request $request): Response
    {
        // Utilisation du Voter pour limiter l'accès
        $event = $coupon->getEvent();
        $this->denyAccessUnlessGranted(CouponVoter::COUPON_MANAGE, $event);

        $form = $this->createForm(CouponForm::class, $coupon);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Coupon modifié avec succès');
            return $this->redirectToRoute('organizer_event_coupons', ['id' => $event->getId()]);
        }

        return $this->render('pages/organizer/coupons/edit.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    #[Route(path: '/delete/{id<\d+>}', name: 'delete', methods: ['DELETE'])]
    public function delete(Coupon $coupon): Response
    {
        // Utilisation du Voter pour limiter l'accès
        $event = $coupon->getEvent();
        $this->denyAccessUnlessGranted(CouponVoter::COUPON_MANAGE, $event);

        return $this->crudAjaxDelete($coupon);
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

        return $this->render( 'pages/organizer/coupons/blocs/_coupon_value.html.twig', [
            'form' => $form->createView(),
        ] );
    }
}