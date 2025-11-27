<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\TeamType;
use App\View\Page;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/team')]
final class TeamController extends AbstractController
{
    private function initBreadCrumbList(?Team $team = null): array
    {
        $breadcrumbList = [[
            'label' => 'Teams',
            'url' => $this->generateUrl('app_team_index')
        ]];
        if ($team) {
            $breadcrumbList[] = [
                'label' => (string)$team,
                'url' => $this->generateUrl('app_team_show', ['id' => $team->id])
            ];
        }
        return $breadcrumbList;
    }

    #[Route(name: 'app_team_index', methods: ['GET'])]
    public function index(): Response
    {
        $page = new Page('Teams');
        $page->breadcrumbList = $this->initBreadCrumbList();
        $page->actions[] = [
            'name' => 'Link',
            'props' => [
                'label' => 'New Team',
                'href' => $this->generateUrl('app_team_new'),
                'class' => 'button button--primary',
                'startIcon' => 'plus',
            ]
        ];

        $page->content = $this->renderView('team/index.html.twig');

        return $this->render('_embed/page.html.twig', [
            'page' => $page,
        ]);
    }

    #[Route('/new', name: 'app_team_new', methods: ['GET', 'POST'])]
    public function new(
        Request                $request,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $team = new Team();
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($team);
            $entityManager->flush();

            return $this->redirectToRoute('app_team_show', ['id' => $team->id], Response::HTTP_SEE_OTHER);
        }

        $formView = $form->createView();

        $page = new Page('New Team');
        $page->breadcrumbList = [
            ...$this->initBreadCrumbList(),
            [
                'label' => 'New',
                'url' => $this->generateUrl('app_team_new')
            ]
        ];
        $page->actions[] = [
            'name' => 'Button',
            'props' => [
                'label' => 'Save',
                'class' => 'button button--primary',
                'type' => 'submit',
                'form' => $formView->vars['id'],
            ]
        ];

        $page->content = $this->renderView('team/new.html.twig', [
            'team' => $team,
            'form' => $formView
        ]);

        return $this->render('_embed/page.html.twig', [
            'page' => $page,
            'form' => $form,
        ]);
    }

    #[Route('/{id:team}', name: 'app_team_show', methods: ['GET'])]
    #[Route('/{id:team}/work-items', name: 'app_team_show_work_items', methods: ['GET'])]
    public function show(
        Team $team,
        Request $request
    ): Response
    {
        $currentRouteName = $request->attributes->get('_route');
        $viewMap = [
            'app_team_show' => [
                'label' => 'Overview',
                'template' => 'team/show/overview.html.twig',
            ],
//            'app_team_show_work_items' => [
//                'label' => 'Work Items',
//                'template' => 'team/show/work_items.html.twig',
//            ],
        ];

        $page = new Page($team->name);
        $page->breadcrumbList = $this->initBreadCrumbList($team);
        $page->actions[] = [
            'name' => 'Link',
            'props' => [
                'label' => 'Edit',
                'href' => $this->generateUrl('app_team_edit', ['id' => $team->id]),
                'class' => 'button',
                'startIcon' => 'pen-to-square',
            ]
        ];
        $page->actions[] = [
            'name' => 'DeleteForm',
            'props' => [
                'entity' => $team,
                'action' => $this->generateUrl('app_team_delete', ['id' => $team->id]),
            ],
        ];

        $page->content = $this->renderView($viewMap[$currentRouteName]['template'], ['team' => $team]);

        $page->menu = [];
        foreach ($viewMap as $name => $value) {
            $page->menu[$name] = [
                'label' => $value['label'],
                'active' => $name === $currentRouteName,
                'url' => $this->generateUrl($name, ['id' => $team->id]),
            ];
        }

        return $this->render('_embed/page.html.twig', [
            'page' => $page,
        ]);
    }

    #[Route('/{id:team}/edit', name: 'app_team_edit', methods: ['GET', 'POST'])]
    public function edit(
        Team                $team,
        Request                $request,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_team_show', ['id' => $team->id], Response::HTTP_SEE_OTHER);
        }

        $formView = $form->createView();

        $page = new Page('Edit Team');
        $page->breadcrumbList = [
            ...$this->initBreadCrumbList($team),
            [
                'label' => 'Edit',
                'url' => $this->generateUrl('app_team_edit', ['id' => $team->id])
            ]
        ];

        $page->actions[] = [
            'name' => 'Button',
            'props' => [
                'label' => 'Save',
                'class' => 'button button--primary',
                'type' => 'submit',
                'form' => $formView->vars['id'],
            ]
        ];

        $page->content = $this->renderView('team/edit.html.twig', [
            'team' => $team,
            'form' => $formView
        ]);

        return $this->render('_embed/page.html.twig', [
            'page' => $page,
            'form' => $formView
        ]);
    }

    #[Route('/{id:team}', name: 'app_team_delete', methods: ['POST'])]
    public function delete(
        Team                $team,
        Request                $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        if ($this->isCsrfTokenValid('delete' . $team->id, $request->getPayload()->getString('_token'))) {
            $entityManager->remove($team);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_team_index', [], Response::HTTP_SEE_OTHER);
    }
}
