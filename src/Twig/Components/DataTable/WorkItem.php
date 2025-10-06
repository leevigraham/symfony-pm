<?php

namespace App\Twig\Components\DataTable;

use App\DTO\DataTable\DataTableFilterDTO;
use Pagerfanta\Pagerfanta;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

#[AsLiveComponent(template: 'work_item/_dataTable.html.twig')]
class WorkItem extends DataTable
{
    #[LiveProp(writable: true, url: true)]
    public ?DataTableFilterDTO $filter = null;

    #[LiveProp(writable: false)]
    public ?string $projectId = null;

    #[LiveProp(writable: false)]
    public ?string $parentId = null;

    public function getPager(): Pagerfanta
    {
        $queryBuilder = $this
            ->entityManager
            ->getRepository(\App\Entity\WorkItem::class)
            ->createQueryBuilder('entity');

        $keywords = $this->filter?->keywords ?? null;
        if ($keywords) {
            $queryBuilder
                ->andWhere('LOWER(entity.title) LIKE :keywords OR LOWER(entity.key) LIKE :keywords')
                ->setParameter('keywords', '%' . mb_strtolower($keywords) . '%');
        }

        if ($this->projectId) {
            $queryBuilder
                ->andWhere('entity.project = :projectId')
                ->setParameter('projectId', $this->projectId);
        }

        if ($this->parentId) {
            $queryBuilder
                ->andWhere('entity.parentWorkItem = :parentId')
                ->setParameter('parentId', $this->parentId);
        }

        return $this->paginateQueryBuilder($queryBuilder);
    }
}
