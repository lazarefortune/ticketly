<?php

namespace App\Domain\Payment\Subscriber;

use App\Domain\Event\Entity\Ticket;
use App\Domain\Payment\Entity\Payment;
use App\Domain\Payment\Event\PaymentFailedEvent;
use App\Domain\Payment\Event\PaymentSuccessEvent;
use App\Domain\Payment\Event\RefundSuccessEvent;
use App\Infrastructure\AppConfigService;
use App\Infrastructure\Mailing\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaymentSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private readonly MailService            $mailService,
        private readonly UrlGeneratorInterface  $urlGenerator,
        private readonly AppConfigService       $appConfigService,
        private readonly EntityManagerInterface $em,
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PaymentSuccessEvent::class => 'onPaymentSuccess',
            PaymentFailedEvent::class => 'onPaymentFailed',
            RefundSuccessEvent::class => 'onRefundSuccess',
        ];
    }

    public function onPaymentSuccess(PaymentSuccessEvent $event): void
    {
        $paymentId = $event->getPaymentId();
        $payment = $this->em->getRepository(Payment::class)->find($paymentId);

        if ($payment) {
            $payment->setStatus(Payment::STATUS_SUCCESS);
            $payment->setUpdatedAt(new \DateTime());
            $payment->setSessionId(null);

            $reservation = $payment->getReservation();
            $tickets = [];

            if ($reservation) {
                $reservation->setStatus(Payment::STATUS_SUCCESS);
                $reservation->setUpdatedAt(new \DateTime());
                $reservation->setBuyAt(new \DateTime());

                $neededTickets = $reservation->getQuantity();

                for ($i = 0; $i < $neededTickets; $i++) {
                    $ticket = new Ticket();
                    $ticket->setReservation($reservation);
                    $ticket->setEvent($reservation->getEvent());
                    $ticket->setBuyAt(new \DateTime());
                    $ticket->setExpiresAt($reservation->getEvent()->getEndDate());

                    $validationUrl = $this->urlGenerator->generate('app_ticket_validation', [
                        'ticketNumber' => $ticket->getTicketNumber(),
                    ], UrlGeneratorInterface::ABSOLUTE_URL);

                    $qrCode = Builder::create()
                        ->writer(new PngWriter())
                        ->data($validationUrl)
                        ->size(300)
                        ->margin(10)
                        ->build();

                    $tempQrCodePath = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
                    $qrCode->saveToFile($tempQrCodePath);

                    // Créer un UploadedFile à partir du fichier généré
                    $file = new UploadedFile(
                        $tempQrCodePath,
                        'qr_code.png', // Nom du fichier
                        mime_content_type($tempQrCodePath),
                        null,
                        true // Marquer le fichier comme déjà déplacé pour éviter les erreurs
                    );

                    $ticket->setQrCodeFile($file);

                    // Définir le nom du fichier dans l'entité Ticket
                    $ticket->setQrCodeName($file->getBasename());

                    $tickets[] = $ticket;
                }
            }

            $this->em->persist($payment);
            $this->em->persist($reservation);
            foreach ($tickets as $ticket) {
                $this->em->persist($ticket);
            }
            $this->em->flush();

            $url = $this->urlGenerator->generate('app_event_reservation_show', [
                'reservationNumber' => $payment->getReservation()->getReservationNumber(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            $email = $this->mailService->createEmail('mails/payment/success.twig', [
                'payment' => $payment,
                'reservation' => $reservation,
                'reservationUrl' => $url,
            ])->to($reservation->getEmail())
                ->subject('Merci pour votre achat !');

            $this->mailService->send($email);
        }
    }

    public function onPaymentFailed(PaymentFailedEvent $event): void
    {
        $paymentId = $event->getPaymentId();
        // Find the payment by id and update the status to failed
        $payment = $this->em->getRepository(Payment::class)->find($paymentId);
        if ($payment) {
            $payment->setStatus(Payment::STATUS_FAILED);
            $payment->setUpdatedAt(new \DateTime());
            $this->em->persist($payment);
            $this->em->flush();
        }
    }

    public function onRefundSuccess(RefundSuccessEvent $event): void
    {
        $payment = $event->getPayment();
        $ticket = $payment->getTicket();

        if ($ticket && ($eventEntity = $ticket->getEvent())) {
            $eventEntity->decrementTakenSpaces();
            $this->em->persist($eventEntity);
            $this->em->flush();

            // Send an email to the user
            $email = $this->mailService->createEmail('mails/payment/refund.twig', [
                'payment' => $payment,
                'ticket' => $ticket,
            ])->to($ticket->getEmail())
                ->subject('Remboursement effectué');

            $this->mailService->send($email);
        }
    }

}
