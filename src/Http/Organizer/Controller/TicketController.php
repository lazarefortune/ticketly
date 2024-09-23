<?php

namespace App\Http\Organizer\Controller;

use App\Domain\Auth\Entity\User;
use App\Domain\Event\Service\TicketService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/ticket', name: 'ticket_')]
#[IsGranted('ROLE_USER')]
class TicketController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TicketService $ticketService,
    ) {
    }

    #[Route('/validation/{ticketNumber?}', name: 'validation')]
    public function validateTicket(Request $request, ?string $ticketNumber = null): Response
    {

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $ticket = null;

        // Retrieve ticket based on provided ticket number or search input
        if ($ticketNumber || $request->query->has('reference')) {
            $ticketNumber = $ticketNumber ?? $request->query->get('reference');
            $ticket = $this->ticketService->getTicketForUser($ticketNumber, $currentUser);
        }

        // Process ticket validation
        if ($request->isMethod('POST') && $ticket && $ticket->isValid() && !$ticket->isUsed()) {
            $ticket->setUsed(true);
            $this->em->flush();

            $this->addFlash('success', 'Ticket validé.');
            return $this->redirectToRoute('organizer_ticket_validation', ['ticketNumber' => $ticket->getTicketNumber()]);
        }

        return $this->render('pages/organizer/ticket/validation.html.twig', [
            'ticket' => $ticket,
        ]);
    }
}
