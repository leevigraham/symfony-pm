<?php

namespace App\View;

use Symfony\Component\HttpFoundation\Response;

class Page
{
    public function __construct(
        public string $title = '',
        public string $subtitle = '',
        public array  $breadcrumbList = [],
        public array  $actions = [],
        public array  $menu = [],
        public array  $classList = [],
        public string $template = '',
        public array  $templateVars = [],
        public Response $response = new Response(),
    )
    {
    }
}