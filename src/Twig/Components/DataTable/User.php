<?php

namespace App\Twig\Components\DataTable;

use App\DTO\DataTable\DataTableFilterDTO;
use Pagerfanta\Pagerfanta;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

#[AsLiveComponent(template: 'user/_dataTable.html.twig')]
class User extends DataTable
{
    #[LiveProp(writable: true, url: true)]
    public ?DataTableFilterDTO $filter = null;

    public function getPager(): Pagerfanta
    {
        $queryBuilder = $this
            ->entityManager
            ->getRepository(\App\Entity\User::class)
            ->createQueryBuilder('entity');

        $keywords = $this->filter?->keywords ?? null;
        if ($keywords) {
            $queryBuilder
                ->andWhere('LOWER(entity.displayName) LIKE :keywords OR LOWER(entity.email) LIKE :keywords')
                ->setParameter('keywords', '%' . mb_strtolower($keywords) . '%');
        }

        return $this->paginateQueryBuilder($queryBuilder);
    }
}
