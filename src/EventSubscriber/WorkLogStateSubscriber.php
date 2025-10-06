<?php

namespace App\EventSubscriber;

use App\Entity\WorkLog;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\EnteredEvent;
use Symfony\Component\Workflow\Event\EnterEvent;

class WorkLogStateSubscriber implements EventSubscriberInterface
{
    public function onWorkLogStarted(EnterEvent $event): void
    {
        /** @var WorkLog $workLog */
        $workLog = $event->getSubject();
        $workLog->setStartedAt(new \DateTimeImmutable());
    }

    public function onWorkLogStopped(EnterEvent $event): void
    {
        /** @var WorkLog $workLog */
        $workLog = $event->getSubject();
        $durationInSeconds = $workLog->getDurationInSeconds() + $workLog->getStartedAt()->diff(new \DateTimeImmutable());
        $workLog->setDurationInSeconds($durationInSeconds);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EnteredEvent::getName('work_log_state', 'started') => 'onWorkLogStarted',
        ];
    }
}
