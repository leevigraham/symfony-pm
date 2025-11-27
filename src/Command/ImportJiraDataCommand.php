<?php

namespace App\Command;

use App\Entity\Project;
use App\Entity\WorkItem;
use App\Entity\WorkLog;
use App\Enum\WorkItemPriority;
use App\Enum\WorkItemStatus;
use App\Repository\ProjectRepository;
use App\Repository\WorkItemRepository;
use App\Repository\WorkLogRepository;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:import-jira-data',
    description: 'Imports data from Jira using the Jira Rest API',
)]
class ImportJiraDataCommand extends Command
{
    private static DateTimeZone $utc;
    private SymfonyStyle $io;
    private ?bool $importWorkItems;
    private ?bool $importWorkLogs;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ProjectRepository      $projectRepository,
        private readonly WorkItemRepository     $workItemRepository,
        private readonly WorkLogRepository      $workLogRepository,
        private readonly HttpClientInterface    $jiraClient,
        private readonly HttpClientInterface    $tempoClient,
    )
    {
        parent::__construct();
    }

    public function __invoke(
        InputInterface                                                                  $input,
        OutputInterface                                                                 $output,
        #[Option(description: "Comma or space separated list of project keys")] ?string $projectKeys = null,
        #[Option(description: "Import Work Items")] bool                                $skipWorkItems = false,
        #[Option(description: "Import Work Logs")] bool                                 $skipWorkLogs = false,
    ): int
    {
        $this->importWorkItems = !$skipWorkItems;
        $this->importWorkLogs = !$skipWorkLogs;

        $this->io = new SymfonyStyle($input, $output);
        $this->io->title('Importing Jira data');

        $this->importOrganisations();
        $projectKeys = $projectKeys ? preg_split('/[\s,]+/', $projectKeys, -1, PREG_SPLIT_NO_EMPTY) : [];
        $this->importProjects($projectKeys);
        return Command::SUCCESS;
    }

    private function importProjects(array $projectKeys = []): void
    {
        $this->io->section('Importing Projects');
        $query = implode('&', array_map(fn($key) => 'keys=' . urlencode($key), $projectKeys));
        $query .= '&maxResults=100';
        $query .= '&expand=description';
        $url = '/rest/api/3/project/search?' . $query;
        $count = 0;
        do {
            $responseData = $this->jiraClient->request('GET', $url)->toArray();

            if (!isset($total)) {
                $total = $responseData['total'];
                $this->io->text("Projects found: $total");
            }

            foreach ($responseData['values'] as $projectData) {
                $count++;
                $progressIndicator = str_pad((string)$count, strlen($total), ' ', STR_PAD_LEFT) . '/' . $total;
                $this->io->text("* {$progressIndicator} - Importing project: {$projectData['name']} ({$projectData['key']})");
                $importKey = $projectData['id'];

                /** @var Project $project */
                $project = $this->projectRepository->findOneBy(['importKey' => $importKey]) ?? new Project();
                $project->importKey = $importKey;
                $project->key = $projectData['key'];
                $project->name = $projectData['name'];
                $project->description = $projectData['description'] ?? null;

                $this->entityManager->persist($project);
                $this->entityManager->flush();
                if ($this->importWorkItems) {
                    $this->importWorkItemsForProject($project);
                }
                if ($this->importWorkLogs) {
                    $this->importWorkLogsForProject($project);
                }
            }

            $url = $responseData['nextPage'] ?? null;

        } while ($url);

        $this->io->info('Projects imported successfully.');
    }

    private function importWorkItemsForProject(Project $project): void
    {
        $responseData = $this->jiraClient->request('POST', '/rest/api/3/search/approximate-count', [
            'json' => [
                'jql' => 'project = ' . $project->key
            ]
        ])->toArray();
        $count = $responseData['count'] ?? 0;
        $this->io->text("- Importing work items: " . $count);

        $url = '/rest/api/3/search/jql';
        $json = [
            'fields' => [
                'id',
                'summary',
//                    'status',
//                    'assignee',
//                    'reporter',
                'timetracking',
                'issuetype',
                'priority',
                'status',
                'parent',
                'created',
            ],
            'expand' => 'children',
            'fieldsByKeys' => true,
            'jql' => 'project = ' . $project->key,
            'maxResults' => 5000
        ];
        do {
            try {
                $responseData = $this->jiraClient->request('POST', $url, ['json' => $json])->toArray();
            } catch (\Exception $e) {
                $this->io->error("Failed to fetch work items for project {$project->key}: " . $e->getMessage());
                return;
            }
            foreach ($responseData['issues'] as $issueData) {
                $importKey = $issueData['id'];
                $createdAt = new DateTime($issueData['fields']['created'], new DateTimeZone('Australia/Sydney'));

                /** @var WorkItem $workItem */
                $workItem = $this->workItemRepository->findOneBy(['importKey' => $importKey]) ?? new WorkItem();
                $workItem->importKey = $importKey;
                $workItem->createdAt = $createdAt->setTimezone(static::getUtc());
                $workItem->key = $issueData['key'];
                $workItem->sequence = explode('-', $issueData['key'])[1];
                $workItem->title = $issueData['fields']['summary'];
                $workItem->priority = match ($issueData['fields']['priority']['name'] ?? null) {
                    'Critical' => WorkItemPriority::CRITICAL,
                    'Major' => WorkItemPriority::HIGH,
                    'Standard' => WorkItemPriority::STANDARD,
                    'Minor / Low' => WorkItemPriority::LOW,
                    default => null,
                };
                $workItem->status = match ($issueData['fields']['status']['statusCategory']['name'] ?? null) {
                    'To Do' => WorkItemStatus::TODO,
                    'In Progress' => WorkItemStatus::IN_PROGRESS,
                    'Done' => WorkItemStatus::DONE,
                    default => null,
                };
                $workItem->project = $this->entityManager->getReference(Project::class, $project->id);
                $workItem->originalEstimateInSeconds = $issueData['fields']['timetracking']['originalEstimateSeconds'] ?? null;
                $workItem->remainingEstimateInSeconds = $issueData['fields']['timetracking']['remainingEstimateSeconds'] ?? null;
                $workItem->timeSpentInSeconds = $issueData['fields']['timetracking']['timeSpentSeconds'] ?? null;

                if (isset($issueData['fields']['parent'])) {
                    $parentImportKey = $issueData['fields']['parent']['id'];
                    $parentWorkItem = $this->workItemRepository->findOneBy(['importKey' => $parentImportKey]);
                    $workItem->parentWorkItem = $parentWorkItem;
                    if (!$parentWorkItem) {
                        $this->io->warning("Parent work item with key {$parentImportKey} not found for work item {$importKey}, skipping parent assignment.");
                    }
                }
                $this->entityManager->persist($workItem);
            }
            $json['nextPageToken'] = $responseData['nextPageToken'] ?? null;
            $this->entityManager->flush();
            $this->entityManager->clear();
        } while ($json['nextPageToken']);

    }

    private function importWorkLogsForProject(Project $project): void
    {
        $this->io->text("- Importing work logs");
        $url = '4/worklogs';
        $query = [
            'projectId' => $project->importKey,
            'limit' => 1000,
        ];
        do {
            $workItems = [];
            $responseData = $this->tempoClient->request('GET', $url, [
                'query' => $query
            ])->toArray();
            foreach ($responseData['results'] as $worklogData) {
                $workItemKey = $worklogData['issue']['id'];
                if (!isset($workItems[$workItemKey])) {
                    $workItem = $this->workItemRepository->findOneBy(['importKey' => $workItemKey]);
                    if (!$workItem) {
                        $this->io->text("Work item with key {$workItemKey} not found, skipping worklog.");
                        continue;
                    }
                    $workItems[$workItemKey] = $workItem;
                }
                $workItem = $workItems[$workItemKey];

                $importKey = $worklogData['tempoWorklogId'];
                $workLog = $this->workLogRepository->findOneBy(['importKey' => $importKey]) ?? new WorkLog();

                $workLog->importKey = $importKey;
                $workLog->workItem = $workItem;
                $workLog->description = $worklogData['description'];
                $workLog->durationInSeconds = $worklogData['timeSpentSeconds'];
                $workLog->billableDurationInSeconds = $worklogData['billableSeconds'];
                $workLog->billable = (bool)$worklogData['billableSeconds'];
                $this->entityManager->persist($workLog);
            }
            $url = $responseData['metadata']['next'] ?? null;
            $query = [];
            $this->entityManager->flush();
            $this->entityManager->clear();
        } while ($url);
    }

    private function importOrganisations(): void
    {
        $this->io->section('Importing Organisations');

        $organisations = [
            "13 Hitech",
            "3 Drunk Monkeys",
            "97 Compression",
            "A. P. Eagers Ltd",
            "A.P. Group Ltd",
            "ActionAid Australia",
            "Agents of Obelisk",
            "Anchor Reef",
            "ANTaR",
            "Arnold Furnace Pty Ltd",
            "ArtBeat",
            "ATO (BAS/IAS)",
            "Ausdance",
            "Australian Red Cross",
            "Avery Plastic Surgery",
            "Baltimore Aircoil Company Australia",
            "Bamboo Growth Pty Ltd",
            "Bang Australia Pty Ltd",
            "Barclay Real Estate",
            "Base Business Pty Ltd",
            "Bibbulmun Track Foundation",
            "Binnie Leasing Group",
            "Bislr Pty Ltd",
            "Boardworld",
            "Brighton Table Tennis Club",
            "Bunnings",
            "Cadence Interactive",
            "Cake Marketing",
            "Campaign Monitor",
            "Camplify",
            "CatholicCare",
            "CleverPatch",
            "Club Evolution Roadside Assistance P/L",
            "Coassemble Pty Ltd",
            "Daracon Engineering",
            "Deckee",
            "Department of Planning & Environment",
            "Destination NSW",
            "Disegno",
            "Easy Signs",
            "Enigma Communication Pty Ltd",
            "Euro RSCG Australia Pty Ltd",
            "Eventarc",
            "Everymind",
            "Five By Five Consulting Pty Ltd",
            "Flotespace",
            "Forgacs Group",
            "Forsythes Human Resources",
            "Forsythes Recruitment",
            "Freshview Pty. Ltd.",
            "Fruit A Peel",
            "GHO Sydney",
            "GitHub",
            "Grigor Lawyers",
            "Growthwise",
            "Halloran Morrissey",
            "Harrys Schnitzels",
            "Headjam",
            "Heffron Consulting",
            "HM Sailing",
            "HNECC Ltd",
            "HOST",
            "Hunter & Central Coast Development Corporation",
            "Hunter New England Local Health District (HNE LHD)",
            "Hunter Primary Care Ltd",
            "Hunter Water Corporation",
            "Hymie Pty Ltd",
            "Icare workers insurance",
            "Impact Communications",
            "iPixel",
            "iris Sydney",
            "Iron Logic",
            "JA Martin",
            "Jack Daniel's Family of Brands",
            "Jamin Day",
            "Kogan Australia Pty Ltd",
            "Labore Pty Ltd atf Tulip Trust",
            "Lake Macquarie City Council",
            "Leah Jay",
            "Liftango",
            "LiteDroid Studios, LLC",
            "M2 Commander",
            "Madebox Pty Ltd",
            "MaintainX",
            "Marketing and Communications, The University of Newcastle",
            "Medical Observer",
            "Merivale",
            "Michelle Bridges Online Partnership",
            "MJR Accountants",
            "Morrin Dental",
            "My Local Foodie",
            "NeatCorp",
            "New Shanghai",
            "Newcastle City Council (Strategy & Engagement)",
            "Newcastle Coal Infrastructure Group",
            "Newism",
            "Newism#338OneWorld",
            "Nexus Lawyers",
            "nib Health Funds Limited",
            "Nic Bezzina",
            "Niche Real Estate",
            "Officeworks",
            "Ogilvy Public Relations",
            "parkrun Australia",
            "parkrun Trading Limited",
            "Paypal",
            "Port Macquarie-Hasting Council",
            "Principle Living",
            "Pulse Communications Pty Ltd",
            "Pulse Mining Systems",
            "Ray White Commercial",
            "River Realty",
            "Rollingball Productions",
            "Royal Flying Doctor Service",
            "RTGS HIGH VALUE PAYMENT REF NO 0322137PARKRUN TRADING L 2010,2067,2069",
            "saberVox Cloud Solutions",
            "Samantha Beattie",
            "Schiller Australia",
            "School of Health Sciences, The University of Newcastle",
            "Slattery Auctions",
            "Social@Ogilvy",
            "St Philip's Christian College",
            "Step Two Designs",
            "Stripe",
            "Teak & Twine",
            "Telstra Pty Ltd",
            "The Anchorage Port Stephens",
            "The Bloomfield Group",
            "The Executive Inn Pty Ltd",
            "The Man Challenge",
            "The University of Newcastle Research Associates Ltd (TUNRA)",
            "The Urban List Trust",
            "The Village of Useful",
            "Ultraviolet Data Services Pty Ltd",
            "University of Newcastle",
            "University of Newcastle (GradSchool)",
            "University of Newcastle (Planning, Quality and Reporting)",
            "Us Sydney",
            "Victor Chang Cardiac Research Institute",
            "Webqem Pty Ltd",
            "Western Suburbs (N'cle) Leagues Club Limited",
            "WhistleOut",
            "Whoooz! Webmedia",
            "Wilde Legal",
            "Wildfire Interactive, Inc.",
            "Zenith Plant Services",
            "Newism",
        ];

        foreach ($organisations as $i => $organisationName) {
            $organisation = new \App\Entity\Organisation();
            $organisation->importKey = "local-$i";
            $organisation->name = $organisationName;
            $this->entityManager->persist($organisation);
        }

        // Finally, flush the changes to the database
        $this->entityManager->flush();
        $this->entityManager->clear();
        $this->io->info('Organisations imported successfully.');
    }

    private static function getUtc(): DateTimeZone
    {
        return self::$utc ??= new DateTimeZone('UTC');
    }
}
