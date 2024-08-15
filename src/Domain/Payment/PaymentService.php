<?php

namespace App\Domain\Payment;

use App\Domain\Event\Entity\Reservation;
use App\Domain\Event\Entity\Ticket;
use App\Domain\Payment\Entity\Payment;
use App\Domain\Payment\Event\PaymentSuccessEvent;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PaymentService
{
    private array $processors;

    public function __construct(
        array $processors,
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
        $this->processors = $processors;
    }

    /**
     * @throws Exception
     */
    public function pay(float $amount, Reservation $reservation, string $paymentMethod): PaymentResult
    {
        $payment = $reservation->getPayment();

        if ($payment) {
            if ($payment->getStatus() === Payment::STATUS_SUCCESS) {
                throw new Exception("La réservation a déjà été payée");
            }
        } else {
            $payment = new Payment();
            $payment->setReservation($reservation);
            $reservation->setPayment($payment);
        }

        $payment->setPaymentMethod($paymentMethod);
        $payment->setAmount($amount);

        return $this->handlePaymentProcessing($payment, $reservation);
    }

    /**
     * @throws Exception
     */
    private function handlePaymentProcessing(Payment $payment, Reservation $reservation): PaymentResult
    {
        foreach ($this->processors as $processor) {
            if ($processor->supports($payment)) {
                $this->validatePayment($payment, $reservation);

                $payment = $this->persistPayment($payment);

                $paymentResult = $processor->processPayment($payment, $reservation);
                if ($paymentResult instanceof PaymentResultDone) {
                    $this->eventDispatcher->dispatch(new PaymentSuccessEvent($payment->getId()));
                }

                return $paymentResult;
            }
        }

        throw new Exception("Méthode de paiement non supportée");
    }

    /**
     * @throws Exception
     */
    private function validatePayment(Payment $payment, Reservation $reservation): void
    {
        if ($payment->getAmount() <= 1) {
            throw new Exception("Le montant doit être supérieur à 1€");
        }
    }

    private function persistPayment(Payment $payment): Payment
    {
        $this->entityManager->persist($payment);
        $this->entityManager->flush();
        return $payment;
    }
}
