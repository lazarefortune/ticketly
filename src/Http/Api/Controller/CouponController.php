<?php

namespace App\Http\Api\Controller;

use App\Domain\Coupon\Entity\Coupon;
use App\Domain\Coupon\Repository\CouponRepository;
use App\Domain\Event\Entity\Reservation;
use App\Domain\Event\Repository\ReservationRepository;
use App\Domain\Payment\Entity\Payment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CouponController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ReservationRepository $reservationRepository,
    ) {
    }

    #[Route('/validate-coupon', name: 'validate_coupon', methods: ['POST'])]
    public function validateCoupon(Request $request, CouponRepository $couponRepository): JsonResponse
    {
        $couponCode = $request->request->get('couponCode');
        $reservationId = $request->request->get('reservationId');

        if (!$couponCode) {
            return new JsonResponse(['success' => false, 'message' => 'Veuillez entrer un code promo.']);
        }

        /** @var Coupon $coupon */
        $coupon = $couponRepository->findOneBy(['code' => $couponCode, 'isActive' => true]);

        if (!$coupon || $coupon->getExpiresAt() < new \DateTime()) {
            return new JsonResponse(['success' => false, 'message' => 'Ce code promo est invalide ou a expiré.']);
        }

        /** @var Reservation $reservation */
        $reservation = $this->reservationRepository->find($reservationId);

        if (!$reservation) {
            return new JsonResponse(['success' => false, 'message' => 'Réservation introuvable.']);
        }

        // Vérifie que le coupon est bien applicable à l'événement de la réservation
        $event = $reservation->getEvent();
        if (!$coupon->getEvent() || $coupon->getEvent()->getId() !== $event->getId()) {
            return new JsonResponse(['success' => false, 'message' => 'Ce code promo est invalide ou a expiré.']);
        }

        // Appliquer le coupon et calculer les montants
        $reservation->applyCoupon($coupon);

        // Supprimer le paiement associé si présent
        $payment = $this->em->getRepository(Payment::class)->findOneBy(['reservation' => $reservation]);
        if ($payment) {
            $payment->setSessionId(null);
            $payment->setUpdatedAt(new \DateTimeImmutable());
            $this->em->persist($payment);
        }

        $this->em->persist($reservation);
        $this->em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Code promo appliqué avec succès !',
            'reservation' => [
                'subTotal' => $reservation->getSubTotal(),
                'serviceCharge' => $reservation->getServiceCharge(),
                'discountAmount' => $reservation->getDiscountAmount(),
                'totalAmount' => $reservation->getTotalAmount()
            ],
            'couponCode' => $coupon->getCode()
        ]);
    }


    #[Route('/remove-coupon', name: 'remove_coupon', methods: ['POST'])]
    public function removeCoupon(Request $request): JsonResponse
    {
        $reservationId = $request->request->get('reservationId');
        /** @var Reservation $reservation */
        $reservation = $this->reservationRepository->find($reservationId);

        if (!$reservation) {
            return new JsonResponse(['success' => false, 'message' => 'Réservation introuvable.']);
        }

        // Retirer le coupon
        $reservation->removeCoupon();

        // Supprimer le paiement associé si présent
        $payment = $this->em->getRepository(Payment::class)->findOneBy(['reservation' => $reservation]);
        if ($payment) {
            $payment->setSessionId(null);
            $payment->setUpdatedAt(new \DateTimeImmutable());
            $this->em->persist($payment);
        }

        // Sauvegarder la réservation mise à jour
        $this->em->persist($reservation);
        $this->em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Coupon retiré avec succès.',
            'reservation' => [
                'subTotal' => $reservation->getSubTotal(),
                'serviceCharge' => $reservation->getServiceCharge(),
                'discountAmount' => $reservation->getDiscountAmount(),
                'totalAmount' => $reservation->getTotalAmount(),
            ],
        ]);
    }

}