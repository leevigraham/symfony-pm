<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\WorkItem;
use App\Form\WorkItemType;
use App\Repository\WorkItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
final class WorkItemController extends AbstractController
{
    #[Route('/work-item', name: 'app_work_item_index', methods: ['GET'])]
    #[Route('/project/{key:project}/work-item', name: 'app_project_work_item_index', methods: ['GET'])]
    public function index(
        WorkItemRepository           $repository,
        ?Project                     $project = null,
    ): Response
    {
        return $this->render('work_item/index.html.twig', [
            'pageTitle' => 'Work Items',
            'project' => $project,
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

            return $this->redirectToRoute('app_work_item_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('work_item/new.html.twig', [
            'pageTitle' => 'New Work Item',
            'work_item' => $workItem,
            'form' => $form,
        ]);
    }

    #[Route('/{key:workItem}', name: 'app_work_item_show', methods: ['GET'])]
    public function show(WorkItem $workItem): Response
    {
        return $this->render('work_item/show.html.twig', [
            'pageTitle' => $workItem->getTitle(),
            'work_item' => $workItem,
        ]);
    }

    #[Route('/{key:workItem}/edit', name: 'app_work_item_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, WorkItem $workItem, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(WorkItemType::class, $workItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_work_item_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('work_item/edit.html.twig', [
            'pageTitle' => 'Edit Work Item',
            'work_item' => $workItem,
            'form' => $form,
        ]);
    }

    #[Route('/{key:workItem}', name: 'app_work_item_delete', methods: ['POST'])]
    public function delete(Request $request, WorkItem $workItem, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$workItem->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($workItem);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_work_item_index', [], Response::HTTP_SEE_OTHER);
    }
}
