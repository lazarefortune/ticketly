<?php
namespace App\Infrastructure\Payment\Cash;

use App\Domain\Payment\Entity\Payment;

class CashRefundProcessor
{
    const PAYMENT_METHOD = 'cash';

    public function supports( Payment $payment ) : bool
    {
        return $payment->getPaymentMethod() === self::PAYMENT_METHOD;
    }

    public function processRefund( Payment $payment ) : bool
    {
        return true;
    }
}