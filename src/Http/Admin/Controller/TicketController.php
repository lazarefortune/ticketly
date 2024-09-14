<?php

namespace App\Http\Admin\Controller;

use App\Domain\Event\Entity\Ticket;
use App\Domain\Event\Repository\TicketRepository;
use App\Domain\Payment\PaymentResultUrl;
use App\Domain\Payment\PaymentService;
use App\Http\Controller\AbstractController;
use App\Infrastructure\Payment\Cash\CashPaymentProcessor;
use App\Infrastructure\Payment\Stripe\StripePaymentProcessor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route( '/tickets' , name: 'ticket_' )]
#[IsGranted( 'ROLE_ADMIN' )]
class TicketController extends AbstractController
{
    public function __construct(
    )
    {
    }

    #[Route( '/paiement/result/{id<\d+>}', name: 'payment_result' )]
    public function paymentResult( Request $request, Ticket $ticket ) : Response
    {
        $paymentSuccess = $request->query->get( 'success' ) === '1';
        $status = $request->query->get( 'success' ) === '1' ? 'success' : 'failure';

        ( $paymentSuccess ) ? $this->addFlash( 'success', 'Le paiement a bien été effectué' ) : $this->addFlash( 'danger', 'Le paiement a échoué' );
        return $this->json('ok');
    }
}