<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectType;
use App\View\Page;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/project')]
final class ProjectController extends AbstractController
{
//    use NamespaceFormTrait;

    private function initBreadCrumbList(?Project $project = null): array
    {
        $breadcrumbList = [[
            'label' => 'Projects',
            'url' => $this->generateUrl('app_project_index')
        ]];
        if ($project) {
            $breadcrumbList[] = [
                'label' => (string)$project,
                'url' => $this->generateUrl('app_project_show', ['key' => $project->key])
            ];
        }
        return $breadcrumbList;
    }

    #[Route(name: 'app_project_index', methods: ['GET'])]
    public function index(): Page
    {
        $page = new Page('Projects');
        $page->breadcrumbList = $this->initBreadCrumbList();
        $page->actions[] = [
            'name' => 'Link',
            'props' => [
                'label' => 'New Project',
                'href' => $this->generateUrl('app_project_new'),
                'class' => 'button button--primary',
                'startIcon' => 'plus',
            ]
        ];

        $page->template = 'project/index.html.twig';
        $page->templateVars = [];

        return $page;
    }

    #[Route('/new', name: 'app_project_new', methods: ['GET', 'POST'])]
    public function new(
        Request                $request,
        EntityManagerInterface $entityManager,
    ): Page|RedirectResponse
    {
        $project = new Project();

        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($project);
            $entityManager->flush();

            return $this->redirectToRoute('app_project_show', ['key' => $project->key], Response::HTTP_SEE_OTHER);
        }

        $page = new Page('New Project');
        $page->classList[] = 'page--narrow';
        $page->breadcrumbList = [
            ...$this->initBreadCrumbList(),
            [
                'label' => 'New',
                'url' => $this->generateUrl('app_project_new')
            ]
        ];
        $page->actions[] = [
            'name' => 'Button',
            'props' => [
                'label' => 'Save',
                'class' => 'button button--primary',
                'type' => 'submit',
                'form' => $form->getName(),
            ]
        ];

        $page->template = 'project/new.html.twig';
        $page->templateVars = [
            'project' => $project,
            'form' => $form
        ];

        return $page;
    }

    #[Route('/{key:project}', name: 'app_project_show', methods: ['GET'])]
    #[Route('/{key:project}/work-items', name: 'app_project_show_work_items', methods: ['GET'])]
    public function show(
        Project $project,
        Request $request
    ): Page
    {
        $currentRouteName = $request->attributes->get('_route');
        $viewMap = [
            'app_project_show' => [
                'label' => 'Overview',
                'template' => 'project/show/overview.html.twig',
            ],
            'app_project_show_work_items' => [
                'label' => 'Work Items',
                'template' => 'project/show/work_items.html.twig',
            ],
        ];

        $page = new Page($project->name);
        $page->breadcrumbList = $this->initBreadCrumbList($project);
        $page->menu = [];
        foreach ($viewMap as $name => $value) {
            $page->menu[$name] = [
                'label' => $value['label'],
                'active' => $name === $currentRouteName,
                'url' => $this->generateUrl($name, ['key' => $project->key]),
            ];
        }

        $page->actions[] = [
            'name' => 'Link',
            'props' => [
                'label' => 'Edit',
                'href' => $this->generateUrl('app_project_edit', ['key' => $project->key]),
                'class' => 'button',
                'startIcon' => 'pen-to-square',
            ]
        ];
        $page->actions[] = [
            'name' => 'DeleteForm',
            'props' => [
                'entity' => $project,
                'action' => $this->generateUrl('app_project_delete', ['key' => $project->key]),
            ],
        ];

        $page->template = $viewMap[$currentRouteName]['template'];
        $page->templateVars = [
            'project' => $project
        ];


        return $page;
    }

    #[Route('/{key:project}/edit', name: 'app_project_edit', methods: ['GET', 'POST'])]
    public function edit(
        Project                $project,
        Request                $request,
        EntityManagerInterface $entityManager,
    ): Page|RedirectResponse
    {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_project_show', ['key' => $project->key], Response::HTTP_SEE_OTHER);
        }

        $page = new Page('Edit Project');
        $page->classList[] = 'page--narrow';
        $page->breadcrumbList = [
            ...$this->initBreadCrumbList($project),
            [
                'label' => 'Edit',
                'url' => $this->generateUrl('app_project_edit', ['key' => $project->key])
            ]
        ];

        $page->actions[] = [
            'name' => 'Button',
            'props' => [
                'label' => 'Save',
                'class' => 'button button--primary',
                'type' => 'submit',
                'form' => $form->getName(),
            ]
        ];

        $page->template = 'project/edit.html.twig';
        $page->templateVars = [
            'project' => $project,
            'form' => $form,
        ];

        return $page;
    }

    #[Route('/{key:project}', name: 'app_project_delete', methods: ['POST'])]
    public function delete(
        Project                $project,
        Request                $request,
        EntityManagerInterface $entityManager
    ): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete' . $project->id, $request->getPayload()->getString('_token'))) {
            $entityManager->remove($project);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_project_index', [], Response::HTTP_SEE_OTHER);
    }
}
