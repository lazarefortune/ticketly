<?php

namespace App\Domain\Payment\Event;

use App\Domain\Payment\Entity\Payment;
use Symfony\Contracts\EventDispatcher\Event;

class RefundSuccessEvent extends Event
{
    public const NAME = 'payment.refund.success';

    private Payment $payment;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    public function getPayment(): Payment
    {
        return $this->payment;
    }
}
