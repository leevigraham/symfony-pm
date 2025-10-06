<?php

namespace App\Command;

use App\Entity\Project;
use App\Entity\WorkItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:import-tempo-data',
    description: 'Imports data from Tempo using the Tempo Rest API',
)]
class ImportTempoDataCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly HttpClientInterface    $tempoClient,
    )
    {
        parent::__construct();
    }

    public function __invoke(
        InputInterface  $input,
        OutputInterface $output,
    ): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->importTimesheets();
        return Command::SUCCESS;
    }

    private function importTimesheets(): void
    {
        $this->io->section('Importing Timesheets');

        $response = $this->tempoClient->request('GET', '4/worklogs');
        $worklogs = $response->toArray()['results'] ?? [];

        $workItemRepository = $this->entityManager->getRepository(WorkItem::class);
        $workItems = [];
        foreach ($worklogs as $worklogData) {
            $workItemKey = 'jira-' . $worklogData['issue']['id'];
            if (!isset($workItems[$workItemKey])) {
                $workItems[$workItemKey] = $workItemRepository->findOneBy(['importKey' => $workItemKey]);
            }
            $workItem = $workItems[$workItemKey];
            if(!$workItem) {
                $this->io->warning("Work item with key {$workItemKey} not found, skipping worklog.");
                continue;
            }
            $workLog = new \App\Entity\WorkLog();
            $workLog->setWorkItem($workItem);
            $workLog->setDescription($worklogData['description']);
            $workLog->setDurationInSeconds($worklogData['timeSpentSeconds']);
            $workLog->setStartedAt(new \DateTimeImmutable());
            $workLog->setBillable(true);
            $this->entityManager->persist($workLog);
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
        $this->io->info('Worklogs imported successfully.');
    }
}
