<?php

namespace App\Twig\Components\DataTable;

use App\Form\DataTable\ProjectFilterType;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\PreReRender;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\PostMount;
use Symfony\UX\TwigComponent\Attribute\PreMount;

#[AsLiveComponent(template: 'project/_dataTable.html.twig', method: 'get')]
class Project extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp]
    public ?string $organisationId = null;

    #[LiveProp]
    public ?string $namespace = null;

    #[LiveProp]
    public ?string $title = null;

    #[LiveProp(writable: true, fieldName: 'getFormName()', url: true)]
    public array $formValues = [];

    #[LiveProp]
    public array $paginationOptions = [];

    public function __construct(
        protected readonly RequestStack           $requestStack,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly FormFactoryInterface   $formFactory,
        protected readonly SerializerInterface    $serializer
    )
    {
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->formFactory->createNamed($this->namespace, ProjectFilterType::class, $this->formValues);
    }

    #[PreMount]
    public function preMount(array $data): array
    {
        // Define a namespace if not set - used for form and query parameters
        $data['namespace'] = $data['namespace'] ?? 'projectDataTable';
        // Get the filter data from the request query parameters
        //$request = $this->requestStack->getCurrentRequest();
        //$data['formValues'] = $request->query->all($data['namespace']) ?: $data['formValues'] ?? [];
        return $data;
    }

    #[PostMount]
    public function postMount(): void
    {
        $this->submitForm(false);
        // Sync form values after mount
        $request = $this->requestStack->getCurrentRequest();
        $this->paginationOptions = [
            'routeName' => $request->attributes->get('_route') ?? '',
            'routeParams' => array_merge($request->attributes->get('_route_params'), [$this->formName => $this->formValues]),
            'pageParameter' => "[{$this->formName}][pagination][page]"
        ];
    }

    #[PreReRender]
    public function preReRender(): void
    {
        $this->paginationOptions['routeParams'] = array_merge(
            $this->paginationOptions['routeParams'],
            [$this->formName => $this->formValues]
        );
    }

    public function getPager(): Pagerfanta
    {
        $queryBuilder = $this
            ->entityManager
            ->getRepository(\App\Entity\Project::class)
            ->createQueryBuilder('entity');

        $keywords = $this->formValues['keywords'] ?? null;
        if ($keywords) {
            $queryBuilder
                ->andWhere('LOWER(entity.name) LIKE :keywords OR LOWER(entity.key) LIKE :keywords')
                ->setParameter('keywords', '%' . mb_strtolower($keywords) . '%');
        }

        $key = $this->formValues['key'] ?? null;
        if ($key) {
            $queryBuilder
                ->andWhere('LOWER(entity.key) = :key')
                ->setParameter('key', mb_strtolower($key));
        }

        $orderBy = $this->formValues['orderBy'] ?? null;
        $orderBy = array_filter($orderBy);
        if ($orderBy) {
            $sort = "entity.{$orderBy['attribute']}";
            $lower = match ($orderBy['attribute']) {
                'createdAt', "updatedAt" => false,
                default => true,
            };
            if ($lower) {
                $sort = $queryBuilder->expr()->lower($sort);
            }
            $queryBuilder->orderBy($sort, $orderBy['direction'] ?? null);
        } else {
            $queryBuilder->orderBy($queryBuilder->expr()->lower('entity.name'), 'ASC');
        }

        if ($this->organisationId) {
            $queryBuilder
                ->andWhere('entity.organisation = :organisationId')
                ->setParameter('organisationId', $this->organisationId);
        }

        return $this->paginateQueryBuilder($queryBuilder);
    }

    protected function paginateQueryBuilder($queryBuilder): Pagerfanta
    {
        $pagination = $this->formValues['pagination'] ?? [];
        $page = (int)($pagination['page'] ?: 1);
        $perPage = (int)($pagination['perPage'] ?: 50);
        return new Pagerfanta(new QueryAdapter($queryBuilder))
            ->setMaxPerPage(min($perPage, 200))
            ->setCurrentPage($page);
    }
}
