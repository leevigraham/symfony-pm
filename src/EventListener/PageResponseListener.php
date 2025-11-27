<?php

namespace App\EventListener;

use App\View\Page;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

final class PageResponseListener
{
    public function __construct(
        private Environment $twig,
    ) {
    }

    #[AsEventListener(event: KernelEvents::VIEW, priority: 0)]
    public function onKernelView(ViewEvent $event): void
    {
        $result = $event->getControllerResult();

        if (! $result instanceof Page) {
            return;
        }

        // Process forms
        foreach($result->templateVars as $key => $value) {
            if($value instanceof FormInterface) {
                $result->templateVars[$key] = $value->createView();
                if($value->isSubmitted() && !$value->isValid()) {
                    $result->response->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }
        }

        // If you want dialog/slideout later, resolve mode from $request here
        $template = match($event->getRequest()->get('X-Layer')) {
            'dialog' => '_embed/dialog.html.twig',
            'sheet' => '_embed/sheet.html.twig',
            'popover' => '_embed/popover.html.twig',
            default => '_embed/page.html.twig',
        };

        $html = $this->twig->render($template, [
            'page' => $result,
        ]);

        // This calls the hook, which gives you the existing or a new Response
        $response = $result->response;
        $response->setContent($html);

        $event->setResponse($response);
    }
}