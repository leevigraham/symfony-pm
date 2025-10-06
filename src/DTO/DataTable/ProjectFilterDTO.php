<?php

namespace App\DTO\DataTable;

use JetBrains\PhpStorm\ArrayShape;

class ProjectFilterDTO extends DataTableFilterDTO
{
    public function __construct(
        public ?string $key = null,
        #[ArrayShape(['attribute' => 'string', 'direction' => 'string'])]
        public ?array $orderBy = null,
    )
    {
        parent::__construct();
    }
}
