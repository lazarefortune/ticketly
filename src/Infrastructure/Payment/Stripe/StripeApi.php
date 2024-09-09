<?php

namespace App\Infrastructure\Payment\Stripe;

use App\Domain\Auth\Entity\User;
use App\Domain\Event\Entity\Reservation;
use App\Domain\Event\Entity\Ticket;
use App\Domain\Payment\Entity\Payment;
use Stripe\AccountLink;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\Invoice;
use Stripe\PaymentIntent;
use Stripe\Plan;
use Stripe\Refund;
use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\Subscription;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeApi
{
    private StripeClient $stripe;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct( string $privateKey )
    {
        Stripe::setApiVersion( '2020-08-27' );
        $this->stripe = new StripeClient( $privateKey );
    }

    /**
     * Crée un customer stripe et sauvegarde l'id dans l'utilisateur.
     * @param array $customerData
     * @return string Client Stripe ID
     * @throws ApiErrorException
     */
    public function createCustomer( array $customerData ) : string
    {
        $client = $this->stripe->customers->create( [
            'metadata' => [
                'email' => (string)$customerData['email'] ?? "",
            ],
            'email' => $customerData['email'] ?? "",
            'name' => $customerData['name'] ?? ""
        ] );

        return $client->id;
    }

    /**
     * @throws ApiErrorException
     */
    public function isCustomerDeleted( string $customerId ) : bool
    {
        $customer = $this->stripe->customers->retrieve( $customerId );
        return $customer->isDeleted();
    }

    /**
     * @throws ApiErrorException
     */
    public function getCustomer( string $customerId ) : Customer
    {
        return $this->stripe->customers->retrieve( $customerId );
    }

    /**
     * @throws ApiErrorException
     */
    public function getPaymentIntent( string $id ) : PaymentIntent
    {
        return $this->stripe->paymentIntents->retrieve( $id );
    }

    /**
     * @throws ApiErrorException
     */
    public function getSession( string $id ) : Session
    {
        return $this->stripe->checkout->sessions->retrieve( $id );
    }

    /**
     * @throws ApiErrorException
     */
    public function getInvoice( string $invoice ) : Invoice
    {
        return $this->stripe->invoices->retrieve( $invoice );
    }

    /**
     * @throws ApiErrorException
     */
    public function getSubscription( string $subscription ) : Subscription
    {
        return $this->stripe->subscriptions->retrieve( $subscription );
    }

    /**
     * @throws ApiErrorException
     */
    public function getPlan( string $plan ) : Plan
    {
        return $this->stripe->plans->retrieve( $plan );
    }

    /**
     * Creates a subscription session and returns the payment URL.
     */
    public function createSubscriptionSession( User $user, string $url ) : string
    {
        // Implement this method to create a subscription session. Need to create Plan entity first.
        return $url;
    }

    /**
     * Crée une session de paiement et renvoie l'URL de paiement.
     * @throws ApiErrorException
     */
    public function createPaymentSession( Payment $payment, Reservation $reservation, string $url ) : string
    {
        $session = $this->stripe->checkout->sessions->create( [
            'cancel_url' => $url . '?success=0',
            'success_url' => $url . '?success=1',
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'customer' => $this->getCustomerByEmail( $reservation->getEmail() )->id,
            'metadata' => [
                'payment_id' => $payment->getId(),
                'reservation_id' => $reservation->getId(),
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'payment_id' => $payment->getId(),
                    'reservation_id' => $reservation->getId(),
                ],
            ],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $reservation->getEvent()->getName(),
                        ],
                        'unit_amount' => $payment->getAmount(),
                    ],
                    'quantity' => 1,
                ],
            ],
        ] );

        return $session->id;
    }

    /**
     * @throws ApiErrorException
     */
    public function getBillingUrl( User $user, string $returnUrl ) : string
    {
        $session = $this->stripe->billingPortal->sessions->create( [
            'customer' => $user->getStripeId(),
            'return_url' => $returnUrl,
        ] );

        return $session->url;
    }

    /**
     * Retrieve a customer by email.
     * @param string $email
     * @return Customer|null
     * @throws ApiErrorException
     */
    public function getCustomerByEmail( string $email ) : ?Customer
    {
        $customers = $this->stripe->customers->all( ['email' => $email] );

        if ( count( $customers->data ) > 0 ) {
            return $customers->data[0];
        }

        return null;
    }

    /**
     * Process a refund for a given charge ID.
     * @param string $chargeId
     * @return Refund
     * @throws ApiErrorException
     */
    public function refundCharge( string $chargeId ) : Refund
    {
        return $this->stripe->refunds->create( [
            'charge' => $chargeId,
        ] );
    }

    public function createAccount( User $user ) : \Stripe\Account
    {
        return $this->stripe->accounts->create( [
            'type' => 'express',
            'country' => 'FR',
            'email' => $user->getEmail(),
            'capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers' => ['requested' => true],
            ],
        ] );
    }

    /**
     * Create a login link for the user's Stripe dashboard.
     * @param User $user
     * @return \Stripe\LoginLink
     * @throws ApiErrorException
     */
    public function createDashboardLink( User $user ) : \Stripe\LoginLink
    {
        return $this->stripe->accounts->createLoginLink( $user->getStripeAccountId() );
    }

    /**
     * Create an account link for the user to complete their account setup.
     * @param string $stripeAccountId
     * @param string $reAuthUrl
     * @param string $returnUrl
     * @return AccountLink
     * @throws ApiErrorException
     */
    public function createAccountLink( string $stripeAccountId, string $reAuthUrl, string $returnUrl ) : AccountLink
    {
        return $this->stripe->accountLinks->create( [
            'account' => $stripeAccountId,
            'refresh_url' => $reAuthUrl,
            'return_url' => $returnUrl,
            'type' => 'account_onboarding',
        ] );
    }

}
