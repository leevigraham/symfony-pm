<?php

namespace App\Twig\Components\DataTable;

use App\DTO\DataTable\DataTableFilterDTO;
use App\DTO\DataTable\ProjectFilterDTO;
use App\Form\DataTable\ProjectFilterType;
use Pagerfanta\Pagerfanta;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

#[AsLiveComponent(template: 'project/_dataTable.html.twig')]
class Project extends DataTable
{
    protected const string FORM_TYPE = ProjectFilterType::class;

    #[LiveProp(writable: true, url: true)]
    public ?ProjectFilterDTO $filter = null;

    public function getPager(): Pagerfanta
    {
        $queryBuilder = $this
            ->entityManager
            ->getRepository(\App\Entity\Project::class)
            ->createQueryBuilder('entity');

        $keywords = $this->filter?->keywords ?? null;
        if ($keywords) {
            $queryBuilder
                ->andWhere('LOWER(entity.name) LIKE :keywords OR LOWER(entity.key) LIKE :keywords')
                ->setParameter('keywords', '%' . mb_strtolower($keywords) . '%');
        }

        $key = $this->filter?->key ?? null;
        if ($key) {
            $queryBuilder
                ->andWhere('LOWER(entity.key) = :key')
                ->setParameter('key', mb_strtolower($key));
        }

        $orderBy = $this->filter?->orderBy ?? null;
        if ($orderBy) {
            $sort = "entity.{$orderBy['attribute']}";
            $lower = match($orderBy['attribute']) {
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

        return $this->paginateQueryBuilder($queryBuilder);
    }
}
