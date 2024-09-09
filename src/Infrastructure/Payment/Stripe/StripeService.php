<?php

namespace App\Infrastructure\Payment\Stripe;

use App\Domain\Auth\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeService
{
    public function __construct(
        private readonly StripeApi $stripeApi,
        private readonly EntityManagerInterface $entityManager,
        private readonly UrlGeneratorInterface $urlGenerator,
    )
    {
    }

    /**
     * Create a Stripe account for the user
     * @param User $user
     * @return string
     */
    public function createAccountLink( User $user ) : string
    {
        $account = $this->stripeApi->createAccount( $user );

        $reAuthUrl = $this->urlGenerator->generate('app_account_profile', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $returnUrl = $this->urlGenerator->generate('app_account_profile', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $accountLink = $this->stripeApi->createAccountLink(
            $account->id,
            $reAuthUrl,
            $returnUrl
        );


        // save account id and account link in database
        $user->setStripeAccountId($account->id);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $accountLink->url;
    }

    public function createDashboardLink(User $user): string
    {
        $dashboardLink = $this->stripeApi->createDashboardLink($user);

        return $dashboardLink->url;
    }
}