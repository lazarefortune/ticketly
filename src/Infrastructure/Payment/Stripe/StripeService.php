<?php

namespace App\Infrastructure\Payment\Stripe;

use App\Domain\Auth\Core\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeService
{
    public function __construct(
        private readonly StripeApi $stripeApi,
        private readonly EntityManagerInterface $entityManager,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {}

    /**
     * Create or retrieve a Stripe account link for the user.
     * @param User $user
     * @return string
     */
    public function createAccountLink(User $user): string
    {
        $reAuthUrl = $this->generateUrl('app_account_profile');
        $returnUrl = $this->generateUrl('app_account_profile');

        if (!$user->getStripeAccountId()) {
            // Create new Stripe account
            $account = $this->stripeApi->createAccount( $user );
            $user->setStripeAccountId( $account->id );
            $this->saveUser( $user );
        }

        return $this->createAccountLinkForUser($user->getStripeAccountId(), $reAuthUrl, $returnUrl);
    }

    /**
     * Generate the Stripe dashboard link for the user.
     * @param User $user
     * @return string
     */
    public function createDashboardLink(User $user): string
    {
        $dashboardLink = $this->stripeApi->createDashboardLink($user);
        return $dashboardLink->url;
    }

    /**
     * Generate a URL for routes
     * @param string $route
     * @return string
     */
    private function generateUrl(string $route): string
    {
        return $this->urlGenerator->generate($route, [], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * Save the user entity after updating.
     * @param User $user
     */
    private function saveUser(User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * Create a new account link for a user
     * @param string $accountId
     * @param string $reAuthUrl
     * @param string $returnUrl
     * @return string
     */
    private function createAccountLinkForUser(string $accountId, string $reAuthUrl, string $returnUrl): string
    {
        $accountLink = $this->stripeApi->createAccountLink($accountId, $reAuthUrl, $returnUrl);
        return $accountLink->url;
    }
}
