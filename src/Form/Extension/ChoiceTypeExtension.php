<?php

namespace App\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChoiceTypeExtension extends AbstractTypeExtension
{
    /**
     * @inheritDoc
     */
    public static function getExtendedTypes(): iterable
    {
        return [ChoiceType::class];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if($view->vars['expanded'] ?? false) {
            $view->vars['row_attr']['data-expanded'] = $view->vars['expanded'];
        }
        if($view->vars['multiple'] ?? false) {
            $view->vars['row_attr']['data-multiple'] = $view->vars['multiple'];
        }
    }
}
