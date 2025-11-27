<?php

namespace App\Controller;

use App\Entity\WorkLog;
use App\Form\WorkLogType;
use App\View\Page;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/work-log')]
final class WorkLogController extends AbstractController
{
    private function initBreadCrumbList(?WorkLog $workLog = null): array
    {
        $breadcrumbList = [[
            'label' => 'Work Logs',
            'url' => $this->generateUrl('app_work_log_index')
        ]];
        if ($workLog) {
            $breadcrumbList[] = [
                'label' => (string)$workLog,
                'url' => $this->generateUrl('app_work_log_show', ['id' => $workLog->id])
            ];
        }
        return $breadcrumbList;
    }

    #[Route(name: 'app_work_log_index', methods: ['GET'])]
    public function index(): Response
    {
        $page = new Page('Work Logs');
        $page->breadcrumbList = $this->initBreadCrumbList();
        $page->actions[] = [
            'name' => 'Link',
            'props' => [
                'label' => 'New Work Log',
                'href' => $this->generateUrl('app_work_log_new'),
                'class' => 'button button--primary',
                'startIcon' => 'plus',
            ]
        ];

        $page->content = $this->renderView('work_log/index.html.twig');

        return $this->render('_embed/page.html.twig', [
            'page' => $page,
        ]);
    }

    #[Route('/new', name: 'app_work_log_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $workLog = new WorkLog();
        $form = $this->createForm(WorkLogType::class, $workLog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($workLog);
            $entityManager->flush();

            return $this->redirectToRoute('app_work_log_show', ['id' => $workLog->id], Response::HTTP_SEE_OTHER);
        }

        $formView = $form->createView();

        $page = new Page('New Work Log');
        $page->breadcrumbList = [
            ...$this->initBreadCrumbList(),
            [
                'label' => 'New',
                'url' => $this->generateUrl('app_work_log_new')
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

        $page->content = $this->renderView('work_log/new.html.twig', [
            'work_log' => $workLog,
            'form' => $formView,
        ]);

        return $this->render('_embed/page.html.twig', [
            'page' => $page,
            'form' => $form,
        ]);
    }

    #[Route('/{id:workLog}', name: 'app_work_log_show', methods: ['GET'])]
    public function show(WorkLog $workLog, Request $request): Response
    {
        $page = new Page($workLog->title ?? 'Work Log');
        $page->breadcrumbList = $this->initBreadCrumbList($workLog);
        $page->actions[] = [
            'name' => 'Link',
            'props' => [
                'label' => 'Edit',
                'href' => $this->generateUrl('app_work_log_edit', ['id' => $workLog->id]),
                'class' => 'button',
                'startIcon' => 'pen-to-square',
            ]
        ];
        $page->actions[] = [
            'name' => 'DeleteForm',
            'props' => [
                'entity' => $workLog,
                'action' => $this->generateUrl('app_work_log_delete', ['id' => $workLog->id]),
            ],
        ];

        $page->content = $this->renderView('work_log/show.html.twig', [
            'work_log' => $workLog,
        ]);

        return $this->render('_embed/page.html.twig', [
            'page' => $page,
        ]);
    }

    #[Route('/{id:workLog}/edit', name: 'app_work_log_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, WorkLog $workLog, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(WorkLogType::class, $workLog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_work_log_show', ['id' => $workLog->id], Response::HTTP_SEE_OTHER);
        }

        $formView = $form->createView();

        $page = new Page('Edit Work Log');
        $page->breadcrumbList = [
            ...$this->initBreadCrumbList($workLog),
            [
                'label' => 'Edit',
                'url' => $this->generateUrl('app_work_log_edit', ['id' => $workLog->id])
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

        $page->content = $this->renderView('work_log/edit.html.twig', [
            'work_log' => $workLog,
            'form' => $formView,
        ]);

        return $this->render('_embed/page.html.twig', [
            'page' => $page,
            'form' => $form,
        ]);
    }

    #[Route('/{id:workLog}', name: 'app_work_log_delete', methods: ['POST'])]
    public function delete(Request $request, WorkLog $workLog, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $workLog->id, $request->getPayload()->getString('_token'))) {
            $entityManager->remove($workLog);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_work_log_index', [], Response::HTTP_SEE_OTHER);
    }
}
