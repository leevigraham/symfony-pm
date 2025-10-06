<?php

namespace App\Twig\Components\DataTable;

use App\DTO\DataTable\DataTableFilterDTO;
use Pagerfanta\Pagerfanta;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

#[AsLiveComponent(template: 'organisation/_dataTable.html.twig')]
class Organisation extends DataTable
{
    #[LiveProp(writable: true, url: true)]
    public ?DataTableFilterDTO $filter = null;

    public function getPager(): Pagerfanta
    {
        $queryBuilder = $this
            ->entityManager
            ->getRepository(\App\Entity\Organisation::class)
            ->createQueryBuilder('entity');

        $keywords = $this->filter?->keywords ?? null;
        if ($keywords) {
            $queryBuilder
                ->andWhere('LOWER(entity.name) LIKE :keywords LIKE :keywords')
                ->setParameter('keywords', '%' . mb_strtolower($keywords) . '%');
        }

        return $this->paginateQueryBuilder($queryBuilder);
    }
}
