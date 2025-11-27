<?php

namespace App\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormTypeExtension extends AbstractTypeExtension
{
    /**
     * @inheritDoc
     */
    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $additionalClassMap = [
            'attr' => match(true) {
                !$view->parent && $options['compound'] => 'form',
                default => 'form-widget',
            },
            'row_attr' => match(true) {
                $form->getParent()?->isRoot() => 'form-row form-type',
                default => 'form-type',
            },
            'error_attr' => 'form-errors',
            'help_attr' => 'form-help',
            'label_attr' => 'form-label',
        ];

        foreach ($additionalClassMap as $key => $class) {
            $existingClasses = $view->vars[$key]['class'] ?? '';
            $view->vars[$key]['class'] = trim($existingClasses . ' ' . $class);
        }

        $blockPrefix = array_slice($view->vars['block_prefixes'], -2)[0];
        $view->vars['row_attr']['data-form-type'] = $blockPrefix;

        $rowId = $view->vars['row_attr']['id'] ?? null;
        if(!$rowId && $view->vars['id']) {
            $view->vars['row_attr']['id'] = $view->vars['id'] . '_row';
        }

        $describedby = $view->vars['attr']['aria-describedby'] ?? '';

        if ($view->vars['help']) {
            $describedby .= " {$view->vars['id']}_help";
        }

        if ($view->vars['errors']->count()){
            $view->vars['error_attr']['id'] = "{$view->vars['id']}_errors";
            $view->vars['attr']['aria-invalid'] = true;
            $describedby .= " {$view->vars['error_attr']['id']}";
        }

        if($describedby) {
            $view->vars['attr']['aria-describedby'] = trim($describedby);
        }
    }
}
