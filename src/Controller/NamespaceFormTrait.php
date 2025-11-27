<?php

namespace App\Controller;

use App\EventSubscriber\FormNamespaceSubscriber;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Contracts\Service\Attribute\Required;

trait NamespaceFormTrait
{
    private ?FormRegistryInterface $formRegistry = null;

    #[Required]
    public function setForm(FormRegistryInterface $formRegistry): void
    {
        $this->formRegistry = $formRegistry;
    }

    protected function createForm(
        string $type,
        mixed  $data = null,
        array  $options = []
    ): FormInterface
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $ns = $request?->attributes->get(FormNamespaceSubscriber::ATTRIBUTE);

        $baseName = $this->formRegistry->getType($type)->getBlockPrefix();
        $name = $baseName . $ns;

        $options['form_namespace'] = $ns;
        $options['block_name'] ??= $ns ? $baseName : null;

        return $this->container->get('form.factory')->createNamed(
            $name,
            $type,
            $data,
            $options
        );
    }
}