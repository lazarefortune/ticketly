<?php

namespace App\Http\Admin\Controller;

use App\Domain\Course\Entity\Technology;
use App\Domain\Course\Repository\TechnologyRepository;
use App\Http\Admin\Data\Crud\TechnologyCrudData;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/technologie', name: 'technology_')]
class TechnologyController extends CrudController
{
    protected string $templatePath = 'technology';
    protected string $menuItem = 'technology';
    protected string $entity = Technology::class;
    protected string $routePrefix = 'admin_technology';
    protected string $searchField = 'name';

    #[Route(path: '/', name: 'index', methods: ['GET'])]
    public function index(TechnologyRepository $repository): Response
    {
        $this->paginator->allowSort('count', 'row.id', 'row.name');
        $query = $repository
            ->createQueryBuilder('row')
            ->leftJoin('row.usages', 'usage')
            ->groupBy('row.id')
            ->orderBy('row.id', 'DESC')
            ->addSelect('COUNT(usage.technology) as count')
            ->setMaxResults(5)
        ;

        return $this->crudIndex($query);
    }

    #[Route(path: '/nouveau', name: 'new', methods: ['POST', 'GET'])]
    public function new(TechnologyRepository $repository): Response
    {
        $technology = new Technology();
        $data = new TechnologyCrudData($technology);

        return $this->crudNew($data);
    }

    #[Route(path: '/{id<\d+>}', name: 'edit', methods: ['POST', 'GET'])]
    public function edit(Technology $technology): Response
    {
        $data = new TechnologyCrudData($technology);

        return $this->crudEdit($data);
    }

    #[Route(path: '/{id<\d+>}', name: 'delete', methods: ['DELETE'])]
    public function delete(Technology $technology): Response
    {
        return $this->crudAjaxDelete($technology);
    }
}