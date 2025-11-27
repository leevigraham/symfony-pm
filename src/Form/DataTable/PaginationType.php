<?php

declare(strict_types=1);

namespace App\Form\DataTable;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaginationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('page', NumberType::class, [
            'label' => 'Page',
            'required' => false,
            'empty_data' => 1,
        ]);
        $builder->add('perPage', NumberType::class, [
            'label' => 'Per Page',
            'required' => false,
            'empty_data' => 50,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'Pagination',
        ]);
    }
}
