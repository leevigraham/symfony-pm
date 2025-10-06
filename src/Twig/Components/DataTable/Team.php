<?php

namespace App\Twig\Components\DataTable;

use App\DTO\DataTable\DataTableFilterDTO;
use Pagerfanta\Pagerfanta;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

#[AsLiveComponent(template: 'team/_dataTable.html.twig')]
class Team extends DataTable
{
    #[LiveProp(writable: true, url: true)]
    public ?DataTableFilterDTO $filter = null;

    public function getPager(): Pagerfanta
    {
        $queryBuilder = $this
            ->entityManager
            ->getRepository(\App\Entity\Team::class)
            ->createQueryBuilder('entity');

        $keywords = $this->filter?->keywords ?? null;
        if ($keywords) {
            $queryBuilder
                ->andWhere('LOWER(entity.name) LIKE :keywords')
                ->setParameter('keywords', '%' . mb_strtolower($keywords) . '%');
        }

        return $this->paginateQueryBuilder($queryBuilder);
    }
}
