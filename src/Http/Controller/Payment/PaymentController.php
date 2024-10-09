<?php

namespace App\Http\Controller\Payment;

use App\Domain\Auth\Core\Entity\User;
use App\Http\Controller\AbstractController;
use App\Infrastructure\Payment\Stripe\StripeService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route( '/paiement', name: 'account_' )]
#[IsGranted( 'ROLE_USER' )]
class PaymentController extends AbstractController
{
    public function __construct(
        private readonly StripeService               $stripeService,
    )
    {
    }

    #[Route('/stripe/connect', name: 'stripe_connect')]
    public function connect(): RedirectResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        // check if user is verified
        if (!$user->isVerified()) {
            $this->addFlash('error', 'Vous devez vérifier votre adresse email avant de pouvoir connecter votre compte Stripe.');
            return $this->redirectToRoute('app_account_profile');
        }

        if ($user->getStripeAccountId() === null || $user->isStripeAccountCompleted() === false) {
            // Crée le lien de connexion Stripe
            $url = $this->stripeService->createAccountLink( $user );

            return $this->redirect($url);
        }

        // L'utilisateur a déjà un compte Stripe
        return $this->redirectToRoute('app_account_profile');
    }

    #[Route('/stripe/dashboard', name: 'stripe_dashboard')]
    public function stripeDashboard(): RedirectResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user->getStripeAccountId()) {
            $this->addFlash('error', 'Vous n\'avez pas encore connecté votre compte Stripe.');
            return $this->redirectToRoute('account_profile');
        }

        // Crée le lien vers le tableau de bord Stripe
        $dashboardUrl = $this->stripeService->createDashboardLink($user);

        return $this->redirect($dashboardUrl);
    }
}