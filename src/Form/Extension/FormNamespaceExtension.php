<?php

namespace App\Form\Extension;

use App\EventSubscriber\FormNamespaceSubscriber;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormNamespaceExtension extends AbstractTypeExtension
{
    private ?string $namespace = null;
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
        $this->namespace = $this->requestStack
            ->getCurrentRequest()
            ->attributes
            ->get(FormNamespaceSubscriber::ATTRIBUTE);
    }

    /**
     * @inheritDoc
     */
    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined('form_namespace');
        $resolver->setAllowedTypes('form_namespace', ['null', 'string']);
        $resolver->setDefault('form_namespace', $this->namespace);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        // Add an ID suffix to the root form to avoid ID collisions
        // when multiple forms are present on the same page
        if (!$view->parent && $options['compound']) {
            $view->vars['id'] .= $options['form_namespace'];
            $view->vars['name'] .= $options['form_namespace'];
        }
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void {
        if (!$view->parent && $options['compound']) {
            $factory = $form->getConfig()->getFormFactory();
            $form = $factory->createNamed(
                FormNamespaceSubscriber::ATTRIBUTE,
                HiddenType::class,
                $options['form_namespace'],
            [
                'block_prefix' => 'namespace',
                'mapped' => false,
            ]);
            $view->children[FormNamespaceSubscriber::ATTRIBUTE] = $form->createView($view);
        }
    }
}
