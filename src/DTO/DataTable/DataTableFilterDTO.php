<?php

namespace App\DTO\DataTable;

use JetBrains\PhpStorm\ArrayShape;

class DataTableFilterDTO implements DataTableFilterDTOInterface
{
    public ?string $id = null;
    public ?string $keywords = null;

    #[ArrayShape(['attribute' => 'string', 'direction' => 'string'])]
    public ?array $orderBy = null;
    #[ArrayShape(['page' => 'string', 'perPage' => 'string'])]
    public ?array $pagination = null;
}
