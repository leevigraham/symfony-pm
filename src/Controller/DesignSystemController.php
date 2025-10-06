<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DesignSystemController extends AbstractController
{
    #[Route('/design-system')]
    public function index(): Response
    {
        return $this->render('design_system/index.html.twig', [
            'pageTitle' => 'Design System',
            'pageDescription' => 'A collection of design components and patterns used across the application.',
        ]);
    }
}
