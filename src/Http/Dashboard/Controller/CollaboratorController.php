<?php

namespace App\Http\Dashboard\Controller;

use App\Domain\Auth\Entity\User;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\Entity\Invitation;
use App\Domain\Event\Form\InviteCollaboratorForm;
use App\Domain\Event\Form\ModifyCollaboratorRolesForm;
use App\Domain\Event\Service\CollaboratorInviteService;
use App\Domain\Event\Service\CollaboratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route( '/event/{id}/collaborators', name: 'event_collaborators_' )]
#[IsGranted( 'EVENT_EDIT', 'event' )]
class CollaboratorController extends AbstractController
{

    public function __construct(
        private readonly CollaboratorInviteService $collaboratorInviteService,
        private readonly CollaboratorService $collaboratorService
    )
    {
    }

    #[Route( '/invite', name: 'invite', methods: ['GET', 'POST'] )]
    public function invite(Event $event, Request $request): Response
    {
        $form = $this->createForm(InviteCollaboratorForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $roles = $form->get('roles')->getData();
            /** @var User $currentUser */
            $currentUser = $this->getUser();

            try {
                $this->collaboratorInviteService->inviteCollaborator($event, $email, $roles, $currentUser);
                $this->addFlash('success', 'Invitation envoyée avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }

            return $this->redirectToRoute('dashboard_event_collaborators_invite', ['id' => $event->getId()]);
        }

        return $this->render('dashboard/event/collaborators/invite_collaborator.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    #[Route('/cancel-invitation/{invitation}', name: 'cancel_invitation', methods: ['DELETE'])]
    public function cancelInvitation(Event $event, Invitation $invitation): JsonResponse
    {
        try {
            $this->collaboratorInviteService->cancelInvitation($invitation);
            return $this->json(['success' => true], 200);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/modify/{collaborator}', name: 'modify', methods: ['GET', 'POST'])]
    public function modify(Event $event, User $collaborator, Request $request): Response
    {
        // Récupérer les rôles actuels du collaborateur
        $eventCollaborator = $this->collaboratorService->findCollaborator($event, $collaborator);
        if (!$eventCollaborator) {
            $this->addFlash('error', 'Collaborateur non trouvé pour cet événement.');
            return $this->redirectToRoute('dashboard_event_collaborators', ['id' => $event->getId()]);
        }

        // Créer le formulaire avec les rôles actuels pré-sélectionnés
        $form = $this->createForm(ModifyCollaboratorRolesForm::class, ['roles' => $eventCollaborator->getRoles()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les nouveaux rôles du formulaire
            $roles = $form->get('roles')->getData();

            // Appeler le service pour modifier les rôles
            $this->collaboratorService->modifyRoles($event, $collaborator, $roles);

            $this->addFlash('success', 'Rôles modifiés avec succès.');
            return $this->redirectToRoute('dashboard_event_collaborators', ['id' => $event->getId()]);
        }

        return $this->render('dashboard/event/collaborators/modify_roles.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
            'collaborator' => $collaborator,
        ]);
    }

    #[Route( '/remove/{collaborator}', name: 'remove', methods: ['DELETE'] )]
    public function ajaxRemove(Event $event, User $collaborator): JsonResponse
    {
        try {
            $this->collaboratorService->removeCollaborator($event, $collaborator);
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->json([
            'success' => true,
        ]);
    }
}
