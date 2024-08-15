<?php

namespace App\Http\Controller;

use App\Domain\Event\Entity\Ticket;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/ticket', name: 'ticket_')]
#[IsGranted('ROLE_ADMIN')]
class TicketController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[Route('/validation/{ticketNumber?}', name: 'validation')]
    public function validateTicket(Request $request, string $ticketNumber = null): Response
    {
        $ticket = null;

        if ($ticketNumber) {
            $ticket = $this->em->getRepository(Ticket::class)->findOneBy(['ticketNumber' => $ticketNumber]);

            if (!$ticket) {
                $this->addFlash('danger', 'Ticket non trouvé.');
            }
        }

        // Si le formulaire de recherche est soumis
        if ($request->isMethod('GET') && $request->query->has('reference')) {
            $ticketNumber = $request->query->get('reference');
            $ticket = $this->em->getRepository(Ticket::class)->findOneBy(['ticketNumber' => $ticketNumber]);

            if (!$ticket) {
                $this->addFlash('danger', 'Ticket non trouvé.');
            }
        }

        // Si la validation manuelle est demandée
        if ($request->isMethod('POST') && $ticket && $ticket->isValid() && !$ticket->isUsed()) {
            $ticket->setUsed(true);
            $this->em->flush();

            $this->addFlash('success', 'Ticket validé.');
            return $this->redirectToRoute('app_ticket_validation', ['ticketNumber' => $ticketNumber]);
        }

        return $this->render('ticket/validation.html.twig', [
            'ticket' => $ticket,
        ]);
    }
}
