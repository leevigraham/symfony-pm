<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\TestType;

final class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'pageTitle' => 'Dashboard',
        ]);
    }

    #[Route('/form-test', name: 'app_form_test')]
    public function formTest(ProjectRepository $projectRepository): Response
    {
        $qb = $projectRepository->createQueryBuilder('project');
        $qb->orderBy('LOWER(project.name)', 'ASC')->setMaxResults(200);
        $projects = $qb->getQuery()->getResult();
        return $this->render('dashboard/form-test.html.twig', [
            'pageTitle' => 'Form Test',
            'form' => $this->createForm(TestType::class),
            'projects' => $projects,
        ]);
    }
}
