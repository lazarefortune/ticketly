<?php

namespace App\Http\Admin\Controller;

use App\Domain\Event\Entity\Reservation;
use App\Http\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_ADMIN')]
#[Route('/reservation', name: 'reservation_')]
class ReservationController extends AbstractController
{
    public function __construct()
    {
    }

    #[Route('/{reservation}/details', name: 'show', methods: ['GET'])]
    public function show( Reservation $reservation ): Response
    {
        return $this->render('admin/reservation/show.html.twig',
            ['reservation' => $reservation]
        );
    }
}