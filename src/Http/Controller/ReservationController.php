<?php

namespace App\Http\Controller;

use App\Domain\Event\Entity\Event;
use App\Domain\Event\Repository\EventRepository;
use App\Domain\Event\Repository\ReservationRepository;
use App\Domain\Reservation\Form\ReservationSearchForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/mes-reservations', name: 'my_reservation_')]
#[IsGranted('ROLE_USER')]
class ReservationController extends AbstractController
{
    public function __construct(
        private readonly ReservationRepository $reservationRepository,
        private readonly EventRepository $eventRepository,
        private readonly EntityManagerInterface $em
    )
    {
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index() : Response
    {
        $reservations = $this->reservationRepository->findByUser( $this->getUser() );
        $suggestedEvents = $this->eventRepository->findNext(5);

        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservations,
            'suggestedEvents' => $suggestedEvents,
        ]);
    }

    #[Route('/recherche', name: 'search', methods: ['GET', 'POST'])]
    public function search(Request $request) : Response
    {
        $form = $this->createForm(ReservationSearchForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reservations = $this->reservationRepository->searchByReservationNumber(
                $this->getUser(),
                $form->get('reservationNumber')->getData()
            );
        } else {
            $reservations = [];
        }

        return $this->render('reservation/search.html.twig', [
            'form' => $form->createView(),
            'reservations' => $reservations,
        ]);
    }

    #[Route('/ajouter/{id}', name: 'add_to_account', methods: ['POST'])]
    public function addToAccount(int $id) : Response
    {
        $reservation = $this->reservationRepository->find($id);

        if (!$reservation) {
            $this->addFlash('danger', 'Réservation introuvable.');
            return $this->redirectToRoute('app_my_reservation_search');
        }

        if ($reservation->getUser() !== null) {
            $this->addFlash('warning', 'Cette réservation est déjà associée à un compte.');
            return $this->redirectToRoute('app_my_reservation_search');
        }

        $reservation->setUser($this->getUser());
        $this->em->persist($reservation);
        $this->em->flush();

        $this->addFlash('success', 'Réservation ajoutée à votre compte avec succès.');

        return $this->redirectToRoute('app_my_reservation_index');
    }
}