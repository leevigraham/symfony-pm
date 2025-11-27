<?php

namespace App\Controller;

use App\Entity\WorkItem;
use App\Form\WorkItemType;
use App\View\Page;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/work-item')]
final class WorkItemController extends AbstractController
{
    private function initBreadCrumbList(?WorkItem $workItem = null): array
    {
        $breadcrumbList = [[
            'label' => 'Work Items',
            'url' => $this->generateUrl('app_work_item_index')
        ]];
        if ($workItem) {
            $breadcrumbList[] = [
                'label' => (string)$workItem,
                'url' => $this->generateUrl('app_work_item_show', ['key' => $workItem->key])
            ];
        }
        return $breadcrumbList;
    }

    #[Route(name: 'app_work_item_index', methods: ['GET'])]
    public function index(): Response
    {
        $page = new Page('Work Items');
        $page->breadcrumbList = $this->initBreadCrumbList();
        $page->actions[] = [
            'name' => 'Link',
            'props' => [
                'label' => 'New Work Item',
                'href' => $this->generateUrl('app_work_item_new'),
                'class' => 'button button--primary',
                'startIcon' => 'plus',
            ]
        ];

        $page->content = $this->renderView('work_item/index.html.twig');

        return $this->render('_embed/page.html.twig', [
            'page' => $page,
        ]);
    }

    #[Route('/new', name: 'app_work_item_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $workItem = new WorkItem();
        $form = $this->createForm(WorkItemType::class, $workItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($workItem);
            $entityManager->flush();

            return $this->redirectToRoute('app_work_item_show', ['key' => $workItem->key], Response::HTTP_SEE_OTHER);
        }

        $formView = $form->createView();

        $page = new Page('New Work Item');
        $page->breadcrumbList = [
            ...$this->initBreadCrumbList(),
            [
                'label' => 'New',
                'url' => $this->generateUrl('app_work_item_new')
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

        $page->content = $this->renderView('work_item/new.html.twig', [
            'work_item' => $workItem,
            'form' => $formView,
        ]);

        return $this->render('_embed/page.html.twig', [
            'page' => $page,
            'form' => $form,
        ]);
    }

    #[Route('/{key:workItem}', name: 'app_work_item_show', methods: ['GET'])]
    #[Route('/{key:workItem}/work-logs', name: 'app_work_item_show_work_logs', methods: ['GET'])]
    public function show(WorkItem $workItem, Request $request): Response
    {
        $currentRouteName = $request->attributes->get('_route');

        $viewMap = [
            'app_work_item_show' => [
                'label' => 'Overview',
                'template' => 'work_item/show/overview.html.twig',
            ],

            'app_work_item_show_work_logs' => [
                'label' => 'Work Logs',
                'template' => 'work_item/show/work_logs.html.twig',
            ],
        ];

        $page = new Page($workItem->title ?? 'Work Item');
        $page->breadcrumbList = $this->initBreadCrumbList($workItem);
        $page->actions[] = [
            'name' => 'Link',
            'props' => [
                'label' => 'Edit',
                'href' => $this->generateUrl('app_work_item_edit', ['key' => $workItem->key]),
                'class' => 'button',
                'startIcon' => 'pen-to-square',
            ]
        ];
        $page->actions[] = [
            'name' => 'DeleteForm',
            'props' => [
                'entity' => $workItem,
                'action' => $this->generateUrl('app_work_item_delete', ['key' => $workItem->key]),
            ],
        ];

        $page->content = $this->renderView($viewMap[$currentRouteName]['template'], ['work_item' => $workItem]);

        $page->menu = [];
        foreach ($viewMap as $name => $value) {
            $page->menu[$name] = [
                'label' => $value['label'],
                'active' => $name === $currentRouteName,
                'url' => $this->generateUrl($name, ['key' => $workItem->key]),
            ];
        }

        return $this->render('_embed/page.html.twig', [
            'form' => null,
            'page' => $page,
        ]);
    }

    #[Route('/{key:workItem}/edit', name: 'app_work_item_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, WorkItem $workItem, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(WorkItemType::class, $workItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_work_item_show', ['key' => $workItem->key], Response::HTTP_SEE_OTHER);
        }

        $formView = $form->createView();

        $page = new Page('Edit Work Item');
        $page->breadcrumbList = [
            ...$this->initBreadCrumbList($workItem),
            [
                'label' => 'Edit',
                'url' => $this->generateUrl('app_work_item_edit', ['key' => $workItem->key])
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

        $page->content = $this->renderView('work_item/edit.html.twig', [
            'work_item' => $workItem,
            'form' => $formView,
        ]);

        return $this->render('_embed/page.html.twig', [
            'page' => $page,
            'form' => $form,
        ]);
    }

    #[Route('/{key:workItem}', name: 'app_work_item_delete', methods: ['POST'])]
    public function delete(Request $request, WorkItem $workItem, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $workItem->id, $request->getPayload()->getString('_token'))) {
            $entityManager->remove($workItem);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_work_item_index', [], Response::HTTP_SEE_OTHER);
    }
}
