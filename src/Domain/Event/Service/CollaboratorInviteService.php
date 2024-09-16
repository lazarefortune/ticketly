<?php

namespace App\Domain\Event\Service;

use App\Domain\Auth\Entity\User;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\Entity\Invitation;
use App\Infrastructure\Mailing\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class CollaboratorInviteService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MailService $mailService,
        private readonly UrlGeneratorInterface $router
    ) {}

    /**
     * @throws Exception
     */
    public function inviteCollaborator( Event $event, string $email, array $roles, User $currentUser): ?Invitation
    {
        // Vérifier que l'utilisateur n'essaie pas de s'inviter lui-même
        if ($currentUser->getEmail() === $email) {
            throw new Exception('Vous ne pouvez pas vous inviter vous-même.');
        }

        // Vérifier si une invitation existe déjà
        $invitation = $this->em->getRepository(Invitation::class)
            ->findOneBy(['event' => $event, 'email' => $email]);

        if ($invitation) {
            throw new Exception('Une invitation a déjà été envoyée à cette adresse pour cet événement.');
        }

        // Vérifier si l'utilisateur existe déjà et est collaborateur
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);

        // Vérifier si l'utilisateur est déjà collaborateur
        if ($user && $event->hasCollaborator($user)) {
            throw new Exception('Cet utilisateur est déjà collaborateur de cet événement.');
        }

        // Créer une nouvelle invitation
        $invitation = new Invitation();
        $invitation->setEmail($email);
        $invitation->setEvent($event);
        $invitation->setToken(bin2hex(random_bytes(32)));
        $invitation->setRoles($roles);

        $this->em->persist($invitation);
        $this->em->flush();

        // Générer le lien d'acceptation
        $link = $this->router->generate(
            'dashboard_collaborator_invitation_accept',
            ['token' => $invitation->getToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // Envoi de l'email
        $this->sendInvitationEmail($invitation, $link);

        return $invitation;
    }

    /**
     * Supprimer une invitation
     *
     * @throws Exception
     */
    public function cancelInvitation(Invitation $invitation): void
    {
        $this->em->remove($invitation);
        $this->em->flush();
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    private function sendInvitationEmail( Invitation $invitation, string $link): void
    {
        $email = $this->mailService->createEmail('mails/event/collaborators/invitation.html.twig', [
            'invitation' => $invitation,
            'link' => $link,
        ])
            ->to($invitation->getEmail())
            ->subject('Invitation à collaborer sur un événement');

        $this->mailService->send($email);
    }
}
