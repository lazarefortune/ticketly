<?php

namespace App\Http\Organizer\Controller;

use App\Domain\Auth\Entity\User;
use App\Domain\Event\Service\InvitationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/invitation', name: 'collaborator_invitation_')]
#[IsGranted('ROLE_USER')]
class InvitationController extends AbstractController
{
    public function __construct(private readonly InvitationService $invitationService) {}

    #[Route('/accept/{token}', name: 'accept')]
    public function accept(string $token): Response
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            $this->invitationService->acceptInvitation($token, $user);
            $this->addFlash('success', 'Vous avez accepté l\'invitation.');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('organizer_event_index');
    }

    #[Route('/decline/{token}', name: 'decline')]
    public function decline(string $token): Response
    {
        try {
            $this->invitationService->declineInvitation($token);
            $this->addFlash('info', 'Vous avez refusé l\'invitation.');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('organizer_event_index');
    }
}
