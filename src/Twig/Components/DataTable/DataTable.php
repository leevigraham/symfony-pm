<?php

namespace App\Twig\Components\DataTable;

use App\DTO\DataTable\DataTableFilterDTO;
use App\Form\DataTable\DataTableFilterType;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

abstract class DataTable extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    protected const string FORM_TYPE = DataTableFilterType::class;

    #[LiveProp(writable: true, url: true)]
    public ?int $page = 1;

    #[LiveProp(writable: true, url: true)]
    public ?int $perPage = null;

    #[LiveProp(writable: false)]
    public ?string $_paginationBaseRoute = null;

    #[LiveProp(writable: false)]
    public ?array $_paginationBaseRouteParams = null;

//    #[LiveProp(writable: true, url: true)]
//    public mixed $filter = null;

    public function __construct(
        protected readonly RequestStack           $requestStack,
        protected readonly EntityManagerInterface $entityManager,
    )
    {
    }

    public function mount(
        ?int $perPage = null,
        ?int $page = null
    ): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $this->perPage = $perPage ?? $request?->query->getInt('perPage', 200);
        $this->page = $page ?? $request?->query->getInt('page', 1);
        $this->_paginationBaseRoute ??= $request?->attributes->get('_route');
        $this->_paginationBaseRouteParams ??= [
            ...$request?->attributes->get('_route_params', []),
            'perPage' => $this->perPage
        ];
    }

    #[LiveAction]
    public function doSearch(): void
    {
        // Submit the form! If validation fails, an exception is thrown
        // and the component is automatically re-rendered with the errors
        $this->submitForm();

        $this->page = 1;
        $this->filter = $this->getForm()->getData();
        $this->addFlash('success', 'Search Updated');
    }

    protected function instantiateForm(): FormInterface
    {
        $this->_paginationBaseRouteParams = [
            ...$this->_paginationBaseRouteParams,
            ...(array)$this->filter,
        ];
//        dd($this->filter);
        return $this->createForm(static::FORM_TYPE, $this->filter);
    }

    protected function paginateQueryBuilder($queryBuilder): Pagerfanta
    {
        return new Pagerfanta(new QueryAdapter($queryBuilder))
            ->setMaxPerPage(min($this->perPage, 500))
            ->setCurrentPage($this->page ?? 1);
    }
}
