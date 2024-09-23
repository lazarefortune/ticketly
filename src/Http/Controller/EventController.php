<?php

declare( strict_types=1 );

namespace App\Http\Controller;

use App\Domain\Event\Entity\Event;
use App\Domain\Event\Entity\Reservation;
use App\Domain\Event\Entity\Ticket;
use App\Domain\Event\Form\ConfirmReservationForm;
use App\Domain\Event\Service\ReservationCleanupService;
use App\Domain\Payment\PaymentResultUrl;
use App\Domain\Payment\PaymentService;
use App\Http\Form\PreBookEvent;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route( '/evenements', name: 'event_' )]
class EventController extends AbstractController
{
    private EntityManagerInterface $em;
    private ReservationCleanupService $reservationCleanupService;

    public function __construct(
        EntityManagerInterface $em,
        ReservationCleanupService $reservationCleanupService ,
        private readonly PaymentService $paymentService,
    )
    {
        $this->em = $em;
        $this->reservationCleanupService = $reservationCleanupService;
    }

    #[Route( '/{slug<[a-z0-9A-Z\-]+>}', name: 'show', methods: ['GET', 'POST'] )]
    public function show( Event $event, string $slug, Request $request, SessionInterface $session ) : Response
    {
        if ( $event->getSlug() !== $slug ) {
            return $this->redirectToRoute( 'event_show', [
                'slug' => $event->getSlug(),
            ], 301 );
        }

        if ( !$event->getIsActive() ) {
            throw $this->createNotFoundException();
        }

        // Supprimer toute réservation existante dans la session
        $this->reservationCleanupService->clearSessionReservation( $session );

        $ticketForm = $this->createForm( PreBookEvent::class, null, [
            'remaining_spaces' => (int)$event->getRemainingSpaces(),
        ] );
        $ticketForm->handleRequest( $request );

        $unitPrice = $event->getPrice();
        $serviceChargePercentage = Reservation::SERVICE_CHARGE_PERCENTAGE; // 5% dans votre classe Reservation
        $serviceCharge = ($unitPrice * $serviceChargePercentage) / 100;

        if ( $ticketForm->isSubmitted() && $ticketForm->isValid() ) {
            $quantity = (int)$ticketForm->getData()['quantity'];
            $remainingSpaces = $event->getRemainingSpaces();

            if ( $quantity > $remainingSpaces ) {
                $this->addFlash( 'danger', 'Il n\'y a pas assez de places disponibles.' );
                return $this->redirectToRoute( 'app_event_show', ['slug' => $event->getSlug()] );
            }

            $unitPrice = $event->getPrice();
            $discountAmount = 0;

            // Créer une nouvelle réservation
            $reservation = new Reservation( $event, $quantity, $unitPrice, $discountAmount);
            $event->incrementTakenSpaces( $quantity );

            // Persister la réservation
            $this->em->persist( $reservation );
            $this->em->flush();

            // Stocker le numéro de la réservation dans la session
            $session->set( 'reservation_number', $reservation->getReservationNumber() );

            // Rediriger vers la page de réservation avec le numéro de réservation dans l'URL
            return $this->redirectToRoute( 'app_event_reservation', [
                'slug' => $event->getSlug(),
                'reservationNumber' => $reservation->getReservationNumber(),
            ] );
        }

        return $this->render( 'pages/public/event/show.html.twig', [
            'event' => $event,
            'ticketForm' => $ticketForm->createView(),
            'serviceCharge' => $serviceCharge,
        ] );
    }

    #[Route( '/{slug<[a-z0-9A-Z\-]+>}/reservation/{reservationNumber}', name: 'reservation', methods: ['GET', 'POST'] )]
    public function reservationDetails( Request $request, Event $event, string $reservationNumber, SessionInterface $session ) : Response
    {
        // Récupérer la réservation via le numéro passé dans l'URL
        $reservation = $this->em->getRepository( Reservation::class )
            ->findOneBy( ['reservationNumber' => $reservationNumber] );

        if ( !$reservation || $reservation->getEvent() !== $event ) {
            $this->addFlash( 'danger', 'Réservation non trouvée.' );
            return $this->redirectToRoute( 'app_event_show', ['slug' => $event->getSlug()] );
        }

        if ( $reservation->isCancelled() ) {
            // Gestion de la réservation expirée
            $this->addFlash( 'error', 'Votre réservation a expiré.' );
            return $this->redirectToRoute( 'app_event_show', ['slug' => $event->getSlug()] );
        }

        if ( $reservation->isPaid() ) {
            // Gestion de la réservation déjà payée
            $this->addFlash( 'info', 'Votre réservation a déjà été payée.' );
            return $this->redirectToRoute( 'app_event_reservation_show', ['reservationNumber' => $reservation->getReservationNumber()] );
        }

        if ($reservation->getCoupon()) {
            // Si un coupon est déjà appliqué, on ne montre pas le champ du code promo
            $confirmReservationForm = $this->createForm(ConfirmReservationForm::class, $reservation, [
                'hide_coupon' => true,
            ]);
        } else {
            $confirmReservationForm = $this->createForm(ConfirmReservationForm::class, $reservation);
        }

        $confirmReservationForm->handleRequest( $request );

        if ( $confirmReservationForm->isSubmitted() && $confirmReservationForm->isValid() ) {
            $newReservation = $confirmReservationForm->getData();
            if ( $this->getUser() ) {
                $newReservation->setUser( $this->getUser() );
            }

            $this->em->persist( $newReservation );
            $this->em->flush();

            try {
                $paymentResult = $this->paymentService->pay( $reservation->getTotalAmount(), $reservation, 'stripe' );

                if ( $paymentResult instanceof PaymentResultUrl ) {
                    return $this->redirect( $paymentResult->getUrl() );
                }

                $this->addFlash( 'success', 'Le paiement a bien été effectué' );
                return $this->redirectToRoute( 'app_event_reservation_show', ['reservationNumber' => $reservation->getReservationNumber()] );
            } catch ( \Exception $e ) {
                $this->addFlash( 'danger', $e->getMessage() );
                return $this->redirectToRoute( 'app_event_reservation_show', ['reservationNumber' => $reservation->getReservationNumber()] );
            }
        }

        return $this->render( 'pages/public/event/reservation_details.html.twig', [
            'event' => $event,
            'reservation' => $reservation,
            'confirmReservationForm' => $confirmReservationForm->createView(),
        ] );
    }

    #[Route( '/reservation/{reservationNumber}', name: 'reservation_show', methods: ['GET', 'POST'] )]
    public function showReservation( Request $request, string $reservationNumber ) : Response
    {
        $reservation = $this->em->getRepository( Reservation::class )
            ->findOneBy( ['reservationNumber' => $reservationNumber] );

        if ( !$reservation ) {
            $this->addFlash( 'danger', 'Réservation non trouvée.' );
            return $this->redirectToRoute( 'app_home' );
        }

        if ( !$reservation->isPaid() ) {
            $this->addFlash( 'error', 'La réservation n\'a pas été payée.' );
            return $this->redirectToRoute( 'app_event_reservation', [
                'slug' => $reservation->getEvent()->getSlug(),
                'reservationNumber' => $reservationNumber
            ] );
        }

        return $this->render( 'pages/public/event/reservation_show.html.twig', [
            'reservation' => $reservation,
            'paymentResult' => $request->query->get( 'success' ) === '1',
        ] );
    }

}
