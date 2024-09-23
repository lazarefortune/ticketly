<?php

namespace App\Http\Organizer\Controller;

use App\Http\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class PageController extends AbstractController
{

    public function __construct(
    )
    {
    }

    #[Route( '/', name: 'home', methods: ['GET'] )]
    public function index() : Response
    {
        return $this->render( 'pages/organizer/index.html.twig');
    }

    #[Route( '/maintenance', name: 'maintenance', methods: ['GET'] )]
    public function maintenance() : Response
    {
        return $this->render( 'admin/layouts/maintenance.html.twig' );
    }
}
