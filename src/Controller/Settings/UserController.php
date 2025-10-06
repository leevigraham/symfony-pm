<?php

declare(strict_types=1);

namespace App\Controller\Settings;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/settings/user-profile', name: 'app_settings_user_profile')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'pageTitle' => 'User Profile',
        ]);
    }
}
