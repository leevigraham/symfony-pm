<?php

namespace App\Twig\Components\DataTable;

use App\DTO\DataTable\DataTableFilterDTO;
use App\Form\DataTable\DataTableFilterType;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Metadata\UrlMapping;
use Symfony\UX\TwigComponent\Attribute\PreMount;

/**
 * @property $filter DataTableFilterDTOInterface|null
 */
abstract class DataTable extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    protected const string FORM_TYPE = DataTableFilterType::class;
    protected const string FILTER_FQDN = DataTableFilterDTO::class;

    public ?string $title = null;

    #[LiveProp]
    public string $namespace = 'datatable';

    #[LiveProp]
    public array $paginationOptions = [];


    public function modifyFilterProp(LiveProp $liveProp, string $propName): LiveProp
    {
        return $liveProp->withUrl(new UrlMapping(as: "$this->namespace[$propName]"));
    }

    public function __construct(
        protected readonly RequestStack           $requestStack,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly FormFactoryInterface   $formFactory,
        protected readonly SerializerInterface    $serializer
    )
    {

    }

    /**
     * Pre-mount the component - Process twig component parameters
     * Convert the filter array into a DTO. This is done so we can have type-hinting and default values.
     */
    #[PreMount]
    public function preMount(array $data): array
    {
        $request = $this->requestStack->getCurrentRequest();
        $namespace = $data['namespace'] ?? $this->namespace;
        $filterData = $request->query->all($namespace)['filter'] ?? $data['filter'] ?? null;
        $data['filter'] = $this->serializer->denormalize($filterData, static::FILTER_FQDN);

        $data['paginationOptions'] = $data['paginationOptions'] ?? [
            'routeName' => $request->attributes->get('_route'),
            'routeParams' => array_merge($request->attributes->get('_route_params'), $this->formValues),
            'pageParameter' => "[$this->namespace][filter][pagination][page]"
        ];
        return $data;
    }

//    public function mount(
//        string $namespace,
//    ): void
//    {
//        $this->namespace = $namespace;
//        $request = $this->requestStack->getCurrentRequest();
//        $form = $this->getForm();
//        $queryStringData = $request->query->all($this->namespace)['filter'] ?? null;
//        if($queryStringData) {
//            $form->submit($queryStringData);
//        }
//    }

//    /**
//     * Post mount the component - Process Form Submission
//     * Submit the form with the filter data from the request query parameters.
//     * We need to get the form name to extract the correct data from the request.
//     */
//    #[PostMount]
//    public function postMount(): void
//    {
//        $request = $this->requestStack->getCurrentRequest();
//        $form = $this->getForm();
//        $queryStringData = $request->query->all($this->namespace)['filter'] ?? null;
//        if($queryStringData) {
//            $form->submit($queryStringData);
//        }
//    }


//    #[LiveAction]
//
//    public function doSearch(): void
//    {
//        $this->submitForm();
////        $this->addFlash('success', 'Search Updated');
//    }

    protected function instantiateForm(): FormInterface
    {
        return $this->formFactory->create(static::FORM_TYPE, $this->filter);
//        return $this->formFactory->createNamed(
//            $this->namespace,
//            static::FORM_TYPE,
//            $this->filter
//        );
    }

    protected function paginateQueryBuilder($queryBuilder): Pagerfanta
    {
        $pagination = $this->filter?->pagination;
        $page = $pagination['page'] ?? 1;
        $perPage = $pagination['perPage'] ?? 50;
        return new Pagerfanta(new QueryAdapter($queryBuilder))
            ->setMaxPerPage(min($perPage, 200))
            ->setCurrentPage($page);
    }
}
