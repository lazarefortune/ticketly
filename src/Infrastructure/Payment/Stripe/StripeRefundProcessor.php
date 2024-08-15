<?php

namespace App\Infrastructure\Payment\Stripe;

use App\Domain\Payment\Entity\Payment;
use App\Domain\Payment\RefundProcessorInterface;
use Exception;

class StripeRefundProcessor implements RefundProcessorInterface
{
    const PAYMENT_METHOD = 'stripe';

    public function __construct(
        private readonly StripeApi $stripeApi,
    )
    {
    }

    public function supports( Payment $payment ) : bool
    {
        return $payment->getPaymentMethod() === self::PAYMENT_METHOD;
    }

    /**
     * @throws Exception
     */
    public function processRefund( Payment $payment ) : bool
    {
        if ( $payment->getStatus() !== Payment::STATUS_SUCCESS ) {
            throw new Exception( 'Seuls les paiements réussis peuvent être remboursés.' );
        }
        if ( $payment->getStripeChargeId() === null ) {
            throw new Exception( 'Impossible de rembourser un paiement sans charge Stripe.' );
        }

        try {
            $refund = $this->stripeApi->refundCharge($payment->getStripeChargeId());
        } catch ( \Exception $e ) {
            throw new Exception( 'Le remboursement a échoué.' );
        }

        return ($refund->status === 'succeeded');
    }
}