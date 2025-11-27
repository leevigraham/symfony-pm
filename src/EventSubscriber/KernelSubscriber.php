<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Twig\Environment;

class KernelSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Environment $twig,
        private readonly Security $security,
    ) {
    }

    public function onRequestEvent(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        /** @var User $user */
        $user = $this->security->getUser();
        $event->getRequest()->setLocale($user?->locale ?: 'en_AU');

        $this->twig
            ->getExtension(\Twig\Extension\CoreExtension::class)
            ->setTimezone($user?->timezone ?: 'Australia/Sydney')
        ;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'onRequestEvent',
        ];
    }
}
