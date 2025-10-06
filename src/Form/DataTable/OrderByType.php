<?php

declare(strict_types=1);

namespace App\Form\DataTable;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderByType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('attribute', ChoiceType::class, [
            'label' => 'Attribute',
            'required' => true,
            'choices' => [
                'Name' => 'name',
                'Key' => 'key',
                'Created At' => 'createdAt',
                'Updated At' => 'updatedAt',
            ]
        ]);
        $builder->add('direction', ChoiceType::class, [
            'label' => 'Direction',
            'required' => true,
            'choices' => [
                'Ascending' => 'ASC',
                'Descending' => 'DESC',
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => 'Order by',
        ]);
    }
}
