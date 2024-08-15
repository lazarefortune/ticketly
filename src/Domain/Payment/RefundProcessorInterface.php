<?php
namespace App\Domain\Payment;
use App\Domain\Payment\Entity\Payment;

interface RefundProcessorInterface
{
    public function supports( Payment $payment ): bool;

    public function processRefund( Payment $payment ): bool;
}