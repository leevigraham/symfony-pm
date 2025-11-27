<?php

namespace App\EventListener;

use App\Entity\WorkItem;
use App\Service\SequenceGeneratorService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PrePersistEventArgs;

#[AsEntityListener(
    event: 'prePersist',
    method: 'generateKey',
    entity: WorkItem::class
)]
class WorkItemEntityListener
{
    public function __construct(
        private readonly SequenceGeneratorService $sequenceGenerator,
    )
    {
    }

    public function generateKey(WorkItem $workItem, PrePersistEventArgs $eventArgs): void
    {
        if ($workItem->key) {
            // If the key is already set, we do not generate a new one.
            return;
        }

        $project = $workItem->project;

        if (null === $project) {
            throw new \LogicException('Cannot generate key for WorkItem without a Project.');
        }

        $projectKey = $project->key;
        $scope = 'project_' . $projectKey;

        $sequence = $this->sequenceGenerator->next($scope);

        $workItem->sequence = $sequence;
        $workItem->key = sprintf('%s-%d', $projectKey, $sequence);
    }
}
