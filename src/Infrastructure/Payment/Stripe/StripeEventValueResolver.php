<?php

namespace App\Infrastructure\Payment\Stripe;

use Stripe\Event;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Injecte un événement stripe dans les action d'un controller en validant la signature.
 */
class StripeEventValueResolver implements ValueResolverInterface
{
    public function __construct(private readonly string $stripeWebhookSecret)
    {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): \Generator
    {
        if (Event::class !== $argument->getType()) {
            return [];
        }
        yield Webhook::constructEvent(
            $request->getContent(false),
            (string) $request->headers->get('stripe-signature'),
            $this->stripeWebhookSecret
        );
    }
}