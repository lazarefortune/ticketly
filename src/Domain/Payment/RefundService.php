<?php

namespace App\Domain\Payment;

use App\Domain\Payment\Entity\Payment;
use App\Domain\Payment\Event\RefundSuccessEvent;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RefundService
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
     * Make a refund for a payment
     * @param Payment $payment
     * @throws Exception
     */
    public function refund( Payment $payment ): bool
    {
        if ( $payment->getStatus() !== Payment::STATUS_SUCCESS ) {
            throw new Exception('Seuls les paiements réussis peuvent être remboursés.');
        }

        if ( $payment->getAmount() !== 0 && $payment->getAmount() < 0.50 ) {
            throw new \InvalidArgumentException('Le montant est trop faible pour être remboursé.');
        }

        foreach ( $this->processors as $processor ) {
            if ( $processor->supports( $payment ) ) {
                $result = $processor->processRefund($payment);
                if ($result) {
                    $payment->setStatus(Payment::STATUS_REFUNDED);
                    $this->entityManager->persist($payment);
                    $this->entityManager->flush();

                    $this->eventDispatcher->dispatch(new RefundSuccessEvent($payment));
                    return true;
                }
            }
        }

        throw new Exception('Aucun service de remboursement n\'a pu être trouvé.');
    }
}
