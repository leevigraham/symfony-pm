<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Uid\Uuid;

class RequestIdSubscriber implements EventSubscriberInterface
{
    public const string ATTRIBUTE = '_request_id';
    public const string HEADER = 'X-Request-Id';

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 255],
            KernelEvents::RESPONSE => ['onKernelResponse', -255],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        // Allow upstream systems to supply an id
        $incoming = $request->headers->get(self::HEADER);

        $id = \is_string($incoming) && $incoming !== ''
            ? $incoming
            : $this->generateId();

        $request->attributes->set(self::ATTRIBUTE, $id);
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

        $id = $request->attributes->get(self::ATTRIBUTE);

        if (\is_string($id) && $id !== '') {
            $response->headers->set(self::HEADER, $id);
        }
    }

    private function generateId(): string
    {
        // Use UUIDv7 if you are on Symfony 6.3+ / PHP 8.2+
        return Uuid::v7()->toRfc4122();

        // Or ULID:
        // return (string) Ulid::generate();
    }
}