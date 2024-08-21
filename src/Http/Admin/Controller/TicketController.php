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
        private readonly PaymentService $paymentService
    )
    {
    }

    #[Route( '/', name: 'index', methods: [ 'GET' ] )]
    public function tickets( TicketRepository $ticketRepository ) : Response
    {

        $tickets = $ticketRepository->findAll();
//        $tickets = [];
        return $this->render('admin/ticket/tickets.html.twig', [
            'tickets' => $tickets,
        ]);
    }

    #[Route( '/{id<\d+>}', name: 'manage' , methods: [ 'GET' ] )]
    public function startPayment( Ticket $ticket, Request $request ) : Response
    {
        $data = $request->request->all();

        if ( isset( $data['amount'] ) && isset( $data['paymentMethod'] ) ) {
            $amount = ( $data['amount'] ) ? (int)$data['amount'] : $ticket->getEvent()->getPrice();
            $paymentMethod = ( $data['paymentMethod'] === StripePaymentProcessor::PAYMENT_METHOD ) ? StripePaymentProcessor::PAYMENT_METHOD : CashPaymentProcessor::PAYMENT_METHOD;
        } else {
            $amountDue = $ticket->getEvent()->getPrice();
            if ( $amountDue > 0 ) {
                $amount = $amountDue;
            } else {
                $amount = $ticket->getEvent()->getPrice();
            }
            $paymentMethod = StripePaymentProcessor::PAYMENT_METHOD;
        }


        try {
            $paymentResult = $this->paymentService->pay( $amount, $ticket, $paymentMethod );

            if ( $paymentResult instanceof PaymentResultUrl ) {
                return $this->redirect( $paymentResult->getUrl() );
            }

            $this->addFlash( 'success', 'Le paiement a bien été effectué' );
            return $this->redirectToRoute( 'app_ticket_manage', ['id' => $ticket->getId()] );
        } catch ( \Exception $e ) {
            $this->addFlash( 'danger', $e->getMessage() );
            return $this->redirectToRoute( 'app_ticket_manage', ['id' => $ticket->getId()] );
        }
    }



    #[Route( '/paiement/result/{id<\d+>}', name: 'payment_result' )]
    public function paymentResult( Request $request, Ticket $ticket ) : Response
    {
        $paymentSuccess = $request->query->get( 'success' ) === '1';
        $status = $request->query->get( 'success' ) === '1' ? 'success' : 'failure';

        ( $paymentSuccess ) ? $this->addFlash( 'success', 'Le paiement a bien été effectué' ) : $this->addFlash( 'danger', 'Le paiement a échoué' );
//        return $this->redirectToRoute( 'app_appointment_manage', ['token' => $ticket->getAccessToken()] );
        return $this->json('ok');
    }
}