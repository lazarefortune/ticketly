<?php

namespace App\Http\Dashboard\Controller;

use App\Domain\Account\Service\UserService;
use App\Http\Controller\AbstractController;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

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
        $cache = new FilesystemAdapter();

        return $this->render( 'dashboard/index.html.twig', [
        ] );
    }

    #[Route( '/maintenance', name: 'maintenance', methods: ['GET'] )]
    public function maintenance() : Response
    {
        return $this->render( 'admin/layouts/maintenance.html.twig' );
    }
}
