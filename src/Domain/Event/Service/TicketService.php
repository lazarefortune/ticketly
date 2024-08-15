<?php

namespace App\Domain\Event\Service;

use App\Domain\Event\Dto\TicketDto;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\Entity\Ticket;
use App\Domain\Event\Repository\TicketRepository;
use App\Domain\Payment\Entity\Payment;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class TicketService
{
    public function __construct(
        private readonly TicketRepository $ticketRepository,
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    public function getEventTicketsQueryBuilder(Event $event): QueryBuilder
    {
        return $this->ticketRepository->getEventTicketsQueryBuilder($event);
    }

    public function createOrUpdateTicket(TicketDto $ticketDto, Event $event): Ticket
    {
        $ticket = $this->ticketRepository->findTicketByEmailAndEvent($ticketDto->getEmail(), $event);

        if ($ticket) {
            return $this->handleExistingTicket($ticket, $ticketDto);
        }

        return $this->createNewTicket($ticketDto, $event);
    }

    private function handleExistingTicket(Ticket $ticket, TicketDto $ticketDto): Ticket
    {
        $payment = $ticket->getPayment();

        if ($payment && $payment->getStatus() === Payment::STATUS_SUCCESS) {
            return $ticket;
        }

        $ticket->setName($ticketDto->getName());
        $ticket->setPhoneNumber($ticketDto->getPhoneNumber());
        $this->entityManager->persist($ticket);
        $this->entityManager->flush();

        return $ticket;
    }

    private function createNewTicket(TicketDto $ticketDto, Event $event): Ticket
    {
        $ticket = new Ticket();
        $ticket->setEvent($event);
        $ticket->setName($ticketDto->getName());
        $ticket->setEmail($ticketDto->getEmail());
        $ticket->setPhoneNumber($ticketDto->getPhoneNumber());

        $this->ticketRepository->save($ticket);

        return $ticket;
    }
}
