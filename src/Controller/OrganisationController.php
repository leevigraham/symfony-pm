<?php

namespace App\Controller;

use App\Entity\Organisation;
use App\Form\OrganisationType;
use App\View\Page;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/organisation')]
final class OrganisationController extends AbstractController
{
    private function initBreadCrumbList(
        ?Organisation $organisation = null
    ): array
    {
        $breadcrumbList = [[
            'label' => 'Organisations',
            'url' => $this->generateUrl('app_organisation_index')
        ]];
        if ($organisation) {
            $breadcrumbList[] = [
                'label' => (string)$organisation,
                'url' => $this->generateUrl('app_organisation_show', ['id' => $organisation->id])
            ];
        }
        return $breadcrumbList;
    }

    #[Route(name: 'app_organisation_index', methods: ['GET'])]
    public function index(): Page
    {
        $page = new Page('Organisations');
        $page->breadcrumbList = $this->initBreadCrumbList();
        $page->actions[] = [
            'name' => 'Link',
            'props' => [
                'label' => 'New Organisation',
                'href' => $this->generateUrl('app_organisation_new'),
                'class' => 'button button--primary',
                'startIcon' => 'plus',
            ]
        ];


        $page->template = 'organisation/index.html.twig';
        $page->templateVars = [];

        return $page;
    }

    #[Route('/new', name: 'app_organisation_new', methods: ['GET', 'POST'])]
    public function new(
        Request                $request,
        EntityManagerInterface $entityManager,
    ): Page|RedirectResponse
    {
        $organisation = new organisation();
        $form = $this->createForm(organisationType::class, $organisation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($organisation);
            $entityManager->flush();

            return $this->redirectToRoute('app_organisation_show', ['id' => $organisation->id], Response::HTTP_SEE_OTHER);
        }

        $page = new Page('New organisation');
        $page->breadcrumbList = [
            ...$this->initBreadCrumbList(),
            [
                'label' => 'New',
                'url' => $this->generateUrl('app_organisation_new')
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


        $page->template = 'organisation/new.html.twig';
        $page->templateVars = [
            'organisation' => $organisation,
            'form' => $form
        ];

        return $page;
    }

    #[Route('/{id:organisation}', name: 'app_organisation_show', methods: ['GET'])]
    #[Route('/{id:organisation}/work-items', name: 'app_organisation_show_projects', methods: ['GET'])]
    public function show(
        Organisation $organisation,
        Request      $request
    ): Page
    {
        $currentRouteName = $request->attributes->get('_route');
        $viewMap = [
            'app_organisation_show' => [
                'label' => 'Overview',
                'template' => 'organisation/show/overview.html.twig',
            ],
            'app_organisation_show_projects' => [
                'label' => 'Projects',
                'template' => 'organisation/show/projects.html.twig',
            ],
        ];

        $page = new Page($organisation->name);
        $page->breadcrumbList = $this->initBreadCrumbList($organisation);
        $page->actions[] = [
            'name' => 'Link',
            'props' => [
                'label' => 'Edit',
                'href' => $this->generateUrl('app_organisation_edit', ['id' => $organisation->id]),
                'class' => 'button',
                'startIcon' => 'pen-to-square',
            ]
        ];
        $page->actions[] = [
            'name' => 'DeleteForm',
            'props' => [
                'entity' => $organisation,
                'action' => $this->generateUrl('app_organisation_delete', ['id' => $organisation->id]),
            ],
        ];

        $page->menu = [];
        foreach ($viewMap as $name => $value) {
            $page->menu[$name] = [
                'label' => $value['label'],
                'active' => $name === $currentRouteName,
                'url' => $this->generateUrl($name, ['id' => $organisation->id]),
            ];
        }

        $page->template = $viewMap[$currentRouteName]['template'];
        $page->templateVars = [
            'organisation' => $organisation
        ];

        return $page;
    }

    #[Route('/{id:organisation}/edit', name: 'app_organisation_edit', methods: ['GET', 'POST'])]
    public function edit(
        Organisation           $organisation,
        Request                $request,
        EntityManagerInterface $entityManager,
    ): Page|RedirectResponse
    {
        $form = $this->createForm(OrganisationType::class, $organisation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_organisation_show', ['id' => $organisation->id], Response::HTTP_SEE_OTHER);
        }

        $page = new Page('Edit Organisation');
        $page->breadcrumbList = [
            ...$this->initBreadCrumbList($organisation),
            [
                'label' => 'Edit',
                'url' => $this->generateUrl('app_organisation_edit', ['id' => $organisation->id])
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

        $page->template = 'organisation/edit.html.twig';
        $page->templateVars = [
            'organisation' => $organisation,
            'form' => $form,
        ];

        return $page;
    }

    #[Route('/{id:organisation}', name: 'app_organisation_delete', methods: ['POST'])]
    public function delete(
        Organisation           $organisation,
        Request                $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        if ($this->isCsrfTokenValid('delete' . $organisation->id, $request->getPayload()->getString('_token'))) {
            $entityManager->remove($organisation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_organisation_index', [], Response::HTTP_SEE_OTHER);
    }
}
