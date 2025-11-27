<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\String\ByteString;

/**
 * Manages form namespace for requests, especially in layered contexts (dialogs, sheets, etc.).
 * Stores/retrieves namespace from request attributes and headers.
 * Generates a new namespace if in a layered request without an existing namespace.
 * Sets the namespace in the response headers if available.
 */
class FormNamespaceSubscriber implements EventSubscriberInterface
{
    public const string ATTRIBUTE = '_form_namespace';
    public const string HEADER = 'X-Form-Namespace';
    public const string LAYER_KEY = 'X-Layer';

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 300],
            KernelEvents::RESPONSE => ['onKernelResponse', -255],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        // 1. Existing namespace: header > query > body
        $namespace =
            $request->headers->get(self::HEADER)
                ?: $request->query->get(self::ATTRIBUTE)
                ?: $request->request->get(self::ATTRIBUTE);

        if (!$namespace) {
            // 2. Detect layer request (header OR GET OR POST param)
            $isLayer =
                $request->headers->has(self::LAYER_KEY) ||
                $request->query->has(self::LAYER_KEY) ||
                $request->request->has(self::LAYER_KEY);

            // 3. If we're in a layer and still have no namespace, generate one
            if ($isLayer) {
                $namespace = '_' . ByteString::fromRandom(12)->toString();
            }
        }

        // 4. Always store attribute (can be null)
        $request->attributes->set(self::ATTRIBUTE, $namespace);
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

        $namespace = $request->attributes->get(self::ATTRIBUTE);

        // Only set header when we actually have a namespace
        if (\is_string($namespace) && $namespace !== '') {
            $response->headers->set(self::HEADER, $namespace);
        }
    }
}
