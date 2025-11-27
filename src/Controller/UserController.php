<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\View\Page;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
final class UserController extends AbstractController
{
    private function initBreadCrumbList(?User $user = null): array
    {
        $breadcrumbList = [[
            'label' => 'Users',
            'url' => $this->generateUrl('app_user_index')
        ]];
        if ($user) {
            $breadcrumbList[] = [
                'label' => (string)$user,
                'url' => $this->generateUrl('app_user_show', ['id' => $user->id])
            ];
        }
        return $breadcrumbList;
    }

    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(): Response
    {
        $page = new Page('Users');
        $page->breadcrumbList = $this->initBreadCrumbList();
        $page->actions[] = [
            'name' => 'Link',
            'props' => [
                'label' => 'New User',
                'href' => $this->generateUrl('app_user_new'),
                'class' => 'button button--primary',
                'startIcon' => 'plus',
            ]
        ];

        $page->content = $this->renderView('user/index.html.twig');

        return $this->render('_embed/page.html.twig', [
            'page' => $page,
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(
        Request                $request,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_show', ['id' => $user->id], Response::HTTP_SEE_OTHER);
        }

        $formView = $form->createView();

        $page = new Page('New User');
        $page->breadcrumbList = [
            ...$this->initBreadCrumbList(),
            [
                'label' => 'New',
                'url' => $this->generateUrl('app_user_new')
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

        $page->content = $this->renderView('user/new.html.twig', [
            'user' => $user,
            'form' => $formView
        ]);

        return $this->render('_embed/page.html.twig', [
            'page' => $page,
            'form' => $form,
        ]);
    }

    #[Route('/{id:user}', name: 'app_user_show', methods: ['GET'])]
    #[Route('/{id:user}/work-items', name: 'app_user_show_work_items', methods: ['GET'])]
    public function show(
        User $user,
        Request $request
    ): Response
    {
        $currentRouteName = $request->attributes->get('_route');
        $viewMap = [
            'app_user_show' => [
                'label' => 'Overview',
                'template' => 'user/show/overview.html.twig',
            ],
        ];

        $page = new Page($user->displayName);
        $page->breadcrumbList = $this->initBreadCrumbList($user);
        $page->actions[] = [
            'name' => 'Link',
            'props' => [
                'label' => 'Edit',
                'href' => $this->generateUrl('app_user_edit', ['id' => $user->id]),
                'class' => 'button',
                'startIcon' => 'pen-to-square',
            ]
        ];
        $page->actions[] = [
            'name' => 'DeleteForm',
            'props' => [
                'entity' => $user,
                'action' => $this->generateUrl('app_user_delete', ['id' => $user->id]),
            ],
        ];

        $page->content = $this->renderView($viewMap[$currentRouteName]['template'], ['user' => $user]);

        $page->menu = [];
        foreach ($viewMap as $name => $value) {
            $page->menu[$name] = [
                'label' => $value['label'],
                'active' => $name === $currentRouteName,
                'url' => $this->generateUrl($name, ['id' => $user->id]),
            ];
        }

        return $this->render('_embed/page.html.twig', [
            'page' => $page,
        ]);
    }

    #[Route('/{id:user}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(
        User                $user,
        Request                $request,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_user_show', ['id' => $user->id], Response::HTTP_SEE_OTHER);
        }

        $formView = $form->createView();

        $page = new Page('Edit User');
        $page->breadcrumbList = [
            ...$this->initBreadCrumbList($user),
            [
                'label' => 'Edit',
                'url' => $this->generateUrl('app_user_edit', ['id' => $user->id])
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

        $page->content = $this->renderView('user/edit.html.twig', [
            'user' => $user,
            'form' => $formView
        ]);

        return $this->render('_embed/page.html.twig', [
            'page' => $page,
            'form' => $formView
        ]);
    }

    #[Route('/{id:user}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(
        User                $user,
        Request                $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->id, $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
