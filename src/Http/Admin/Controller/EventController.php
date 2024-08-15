<?php

namespace App\Http\Admin\Controller;

use App\Domain\Event\Dto\TicketDto;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\Form\BuyTicketForm;
use App\Domain\Event\Repository\EventRepository;
use App\Domain\Event\Repository\ReservationRepository;
use App\Domain\Event\Repository\TicketRepository;
use App\Domain\Event\Service\TicketService;
use App\Domain\Payment\Entity\Payment;
use App\Domain\Payment\PaymentResultUrl;
use App\Domain\Payment\PaymentService;
use App\Domain\Payment\RefundService;
use App\Http\Admin\Data\Crud\EventCrudData;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/evenements', name: 'event_')]
#[IsGranted('ROLE_ADMIN')]
class EventController extends CrudController
{
    protected string $templatePath = 'event';
    protected string $menuItem = 'event';
    protected string $entity = Event::class;
    protected string $routePrefix = 'admin_event';
    protected bool $indexOnSave = false;
    protected array $events = [];

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(EventRepository $eventRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $eventRepository->createQueryBuilder('row')
            ->orderBy('row.createdAt', 'DESC');

        return $this->crudIndex($query);
    }

    #[Route('/new', name: 'new', methods: ['POST', 'GET'])]
    public function new(): Response
    {
        $event = new Event();
        $data = new EventCrudData($event);

        return $this->crudNew($data);
    }

    #[Route('/{id<\d+>}', name: 'edit', methods: ['POST', 'GET'])]
    public function edit(Event $event): Response
    {
        $data = new EventCrudData($event);

        return $this->crudEdit($data);
    }

    #[Route('/{id<\d+>}/ajax-delete', name: 'delete', methods: ['DELETE'])]
    public function delete(Event $event): Response
    {
        return $this->crudAjaxDelete($event);
    }

    #[Route('/{id<\d+>}/details', name: 'show', methods: ['GET'])]
    public function show(Event $event, TicketRepository $ticketRepository, ReservationRepository $reservationRepository): Response
    {
        // count tickets
        $nbTickets = $ticketRepository->countTicketsForEvent($event);
        // sum total amount of reservations
        $totalAmountIncome = $reservationRepository->sumReservationsAmountForEvent($event);

        $nbReservations = $reservationRepository->countReservationsForEvent($event);

        $reservations = $reservationRepository->findBy(['event' => $event], ['createdAt' => 'DESC'], 5);

        return $this->render('admin/event/show.html.twig', [
            'event' => $event,
            'nbTickets' => $nbTickets,
            'nbReservations' => $nbReservations,
            'totalAmountIncome' => $totalAmountIncome,
            'reservations' => $reservations,
        ]);
    }

    #[Route('/{id<\d+>}/buy-ticket/{ticketId<\d+>?0}', name: 'buy_ticket', methods: ['GET', 'POST'])]
    public function buyTicket(Event $event, Request $request, TicketService $ticketService, PaymentService $paymentService, TicketRepository $ticketRepository, int $ticketId = 0): Response
    {
        // check if event as available space
        if ($event->getMaxSpace() && $event->getRemainingSpaces() === 0) {
            $this->addFlash('danger', 'Il n\'y a plus de place disponible pour cet événement');
            return $this->redirectToRoute('admin_event_show', ['id' => $event->getId()]);
        }

        $ticket = $ticketId ? $ticketRepository->find($ticketId) : null;
        $form = $this->createForm(BuyTicketForm::class, $ticket ? new TicketDto($ticket) : null);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $paymentMethod = $form->get('paymentMethod')->getData();

                $userData = $form->getData();
                $ticket = $ticketService->createOrUpdateTicket($userData, $event);

                $paymentResult = $paymentService->pay($event->getPrice(), $ticket, $paymentMethod);

                if ($paymentResult instanceof PaymentResultUrl) {
                    return $this->redirect($paymentResult->getUrl());
                }

                $this->addFlash('success', 'Le paiement a bien été effectué');
                return $this->redirectToRoute('admin_event_show', ['id' => $event->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->render('admin/event/buy_ticket.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id<\d+>}/refund', name: 'refund', methods: ['POST'])]
    public function refund(Payment $payment, RefundService $refundService): Response
    {
        try {
            $isRefund = $refundService->refund($payment);
            ($isRefund) ?
                $this->addFlash('success', 'Le remboursement a été effectué avec succès.') :
                $this->addFlash('danger', 'Échec du remboursement');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Échec du remboursement : ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_event_show', ['id' => $payment->getTicket()->getEvent()->getId()]);
    }
}

