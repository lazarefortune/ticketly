<?php

namespace App\Domain\Payment;

use App\Domain\Appointment\Entity\Appointment;
use App\Domain\Event\Entity\Reservation;
use App\Domain\Event\Entity\Ticket;
use App\Domain\Payment\Entity\Payment;

interface PaymentProcessorInterface
{
    /**
     * Check if the payment is supported by the processor
     * @param Payment $payment
     * @return bool
     */
    public function supports( Payment $payment ) : bool;

    /**
     * Process the payment
     * @param Payment $payment
     * @param Reservation $reservation
     * @return PaymentResult
     */
    public function processPayment( Payment $payment, Reservation $reservation ) : PaymentResult;
}