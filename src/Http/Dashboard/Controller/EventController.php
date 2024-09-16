<?php

namespace App\Http\Dashboard\Controller;

use App\Domain\Auth\Entity\User;
use App\Domain\Coupon\Repository\CouponRepository;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\Form\EventForm;
use App\Domain\Event\Repository\EventRepository;
use App\Domain\Event\Repository\InvitationRepository;
use App\Domain\Event\Repository\ReservationRepository;
use App\Domain\Event\Repository\TicketRepository;
use App\Http\Admin\Controller\CrudController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/evenements', name: 'event_')]
#[IsGranted('ROLE_USER')]
class EventController extends CrudController
{
    protected string $templateDirectory = 'dashboard';
    protected string $templatePath = 'event';
    protected string $menuItem = 'event';
    protected string $entity = Event::class;
    protected string $routePrefix = 'dashboard_event';
    protected bool $indexOnSave = false;
    protected array $events = [];

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(EventRepository $eventRepository): Response
    {
        $query = $eventRepository->getQueryEventsByUser($this->getUser());

        return $this->crudIndex($query);
    }

    #[Route('/new', name: 'new', methods: ['POST', 'GET'])]
    public function new(Request $request): Response
    {
        if ( !$this->canCreate( $this->getUser() ) ) {
            $this->addFlash('info', 'Veuillez lier votre compte stripe');
            return $this->redirectToRoute('app_account_profile');
        }

        $event = new Event();
        $form = $this->createForm(EventForm::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event->setOrganizer($this->getUser());
            $this->em->persist($event);
            $this->em->flush();

            $this->addFlash('success', 'L\'événement a été créé avec succès');
            return $this->redirectToRoute('dashboard_event_edit', ['id' => $event->getId()]);
        }

        return $this->render('dashboard/event/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id<\d+>}', name: 'edit', methods: ['POST', 'GET'])]
    public function edit(Event $event, Request $request): Response
    {
        $this->denyAccessUnlessGranted('EVENT_EDIT', $event);

        $form = $this->createForm(EventForm::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event->setUpdatedAt(new \DateTime());
            $this->em->flush();

            $this->addFlash('success', 'L\'événement a été modifié avec succès');
            return $this->redirectToRoute('dashboard_event_edit', ['id' => $event->getId()]);
        }

        return $this->render('dashboard/event/edit.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    #[Route('/{id<\d+>}/ajax-delete', name: 'delete', methods: ['DELETE'])]
    public function delete(Event $event): Response
    {
        $this->denyAccessUnlessGranted('EVENT_DELETE', $event);

        return $this->crudAjaxDelete($event);
    }

    #[Route('/{id<\d+>}/details', name: 'overview', methods: ['GET'])]
    public function show(
        Event $event,
        TicketRepository $ticketRepository,
        ReservationRepository $reservationRepository,
        CouponRepository $couponRepository,
        InvitationRepository $invitationRepository
    ): Response
    {
        $this->denyAccessUnlessGranted('EVENT_VIEW', $event);

        // Count tickets
        $nbTickets = $ticketRepository->countTicketsForEvent($event);
        // Sum total amount of reservations
        $totalAmountIncome = $reservationRepository->sumReservationsAmountForEvent($event);
        $nbReservations = $reservationRepository->countReservationsForEvent($event);

        return $this->render('dashboard/event/overview.html.twig', [
            'event' => $event,
            'nbTickets' => $nbTickets,
            'nbReservations' => $nbReservations,
            'totalAmountIncome' => $totalAmountIncome,
        ]);
    }

    #[Route('/{id<\d+>}/reservations', name: 'reservations', methods: ['GET'])]
    public function reservations(Event $event, ReservationRepository $reservationRepository): Response
    {
        $this->denyAccessUnlessGranted('RESERVATION_VIEW', $event);

        $reservations = $reservationRepository->findBy(['event' => $event], ['createdAt' => 'DESC']);

        return $this->render('dashboard/event/reservations.html.twig', [
            'event' => $event,
            'reservations' => $reservations,
        ]);
    }

    #[Route('/{id<\d+>}/collaborateurs', name: 'collaborators', methods: ['GET'])]
    public function collaborations(Event $event, InvitationRepository $invitationRepository): Response
    {
        $this->denyAccessUnlessGranted('EVENT_INVITE_COLLABORATOR', $event);

        $collaborationInvitations = $invitationRepository->findByEvent($event);

        return $this->render('dashboard/event/collaborators.html.twig', [
            'event' => $event,
            'collaborationInvitations' => $collaborationInvitations,
        ]);
    }

    #[Route('/{id<\d+>}/coupons', name: 'coupons', methods: ['GET'])]
    public function coupons(Event $event, CouponRepository $couponRepository): Response
    {
        $this->denyAccessUnlessGranted('COUPON_MANAGE', $event);

        $coupons = $couponRepository->findByEventId($event->getId());

        return $this->render('dashboard/event/coupons.html.twig', [
            'event' => $event,
            'coupons' => $coupons,
        ]);
    }

    #[Route( '/preview/{slug<[a-z0-9A-Z\-]+>}', name: 'preview', methods: ['GET', 'POST'] )]
    public function preview(Event $event, string $slug): Response
    {
        if ($event->getSlug() !== $slug) {
            return $this->redirectToRoute('event_show', [
                'slug' => $event->getSlug(),
            ], 301);
        }

        $this->denyAccessUnlessGranted('EVENT_VIEW', $event);

        $this->addFlash('info', 'Vous êtes en mode prévisualisation.');

        return $this->render('event/preview.html.twig', [
            'event' => $event
        ]);
    }

    private function canCreate( User $user ) : bool
    {
        return $user->isStripeAccountCompleted();
    }

}
