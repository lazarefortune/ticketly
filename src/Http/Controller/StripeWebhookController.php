<?php

namespace App\Http\Controller;

use App\Domain\Auth\Repository\UserRepository;
use App\Domain\Payment\Entity\Payment;
use App\Domain\Payment\Event\PaymentFailedEvent;
use App\Domain\Payment\Event\PaymentSuccessEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class StripeWebhookController extends AbstractController
{
    private string $stripeWebhookSecret;

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        ParameterBagInterface $parameterBag,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository
    )
    {
        $this->stripeWebhookSecret = $parameterBag->get('stripe_webhook_secret');
    }

    #[Route('/stripe/webhooks', name: 'stripe_webhook')]
    public function handle(Request $request): Response
    {
        $payload = $request->getContent();
        $sig_header = $request->headers->get('Stripe-Signature');

        $event = null;

        try {
            // Vérifiez la signature de la requête avec la clé secrète d'endpoint de votre webhook
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $this->stripeWebhookSecret
            );
        } catch (\UnexpectedValueException $e) {
            // Signature invalide
            return new Response('Invalid request', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Signature invalide
            return new Response('Invalid request', 400);
        }

        // Gérez l'événement
        switch ($event->type) {
            /*
            case 'checkout.session.completed':
                $data = $event->data['object'];
                $paymentId = $data->metadata->payment_id;
                $this->handlePaymentSuccess($paymentId);
                break;
            */
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                $paymentId = $paymentIntent->metadata->payment_id;
                $chargeId = $paymentIntent->latest_charge;
                $this->handlePaymentSuccess($paymentId, $chargeId);
                break;
            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;
                $paymentId = $paymentIntent->metadata->payment_id;
                $this->handlePaymentFailure($paymentId);
                break;
            case 'account.updated':
                $account = $event->data->object;
                $this->handleAccountUpdated($account);
                break;
        }

        return new Response('Received event', 200);
    }

    private function handlePaymentSuccess(string $paymentId, string $chargeId = ""): void
    {
        $payment = $this->entityManager->getRepository(Payment::class)->find($paymentId);
        if ($payment && $payment->getStatus() !== Payment::STATUS_SUCCESS) {
            $payment->setStripeChargeId($chargeId);
            $this->entityManager->persist($payment);
            $this->entityManager->flush();

            $this->eventDispatcher->dispatch(new PaymentSuccessEvent($paymentId));
        }
    }

    private function handlePaymentFailure(string $paymentId): void
    {
        $payment = $this->entityManager->getRepository(Payment::class)->find($paymentId);
        if ($payment && $payment->getStatus() !== Payment::STATUS_FAILED) {
            $this->eventDispatcher->dispatch(new PaymentFailedEvent($paymentId));
        }
    }

    private function handleAccountUpdated( $account ): void
    {
        // Vérifie si le compte Stripe peut maintenant accepter les paiements
        if ($account->charges_enabled) {
            $user = $this->userRepository->findOneBy(['stripeAccountId' => $account->id]);

            if ($user && !$user->isStripeAccountCompleted()) {
                $user->setStripeAccountCompleted(true);
                $this->entityManager->persist($user);
                $this->entityManager->flush();
            }
        }
    }
}
