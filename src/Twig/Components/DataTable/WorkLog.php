<?php

namespace App\Twig\Components\DataTable;

use App\DTO\DataTable\DataTableFilterDTO;
use Pagerfanta\Pagerfanta;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

#[AsLiveComponent(template: 'work_log/_dataTable.html.twig')]
class WorkLog extends DataTable
{
    #[LiveProp(writable: true, url: true)]
    public ?DataTableFilterDTO $filter = null;

    #[LiveProp(writable: false)]
    public ?string $workItemId = null;

    public function getPager(): Pagerfanta
    {
        $queryBuilder = $this
            ->entityManager
            ->getRepository(\App\Entity\WorkLog::class)
            ->createQueryBuilder('entity');

        $keywords = $this->filter?->keywords ?? null;
        if ($keywords) {
            $queryBuilder
                ->leftJoin('entity.workItem', 'workItem')
                ->leftJoin('workItem.project', 'project')
                ->andWhere('LOWER(entity.description) LIKE :keywords OR LOWER(project.key) LIKE :keywords')
                ->setParameter('keywords', '%' . mb_strtolower($keywords) . '%');
        }

        if ($this->workItemId) {
            $queryBuilder
                ->andWhere('entity.workItem = :workItemId')
                ->setParameter('workItemId', $this->workItemId);
        }

        return $this->paginateQueryBuilder($queryBuilder);
    }
}
