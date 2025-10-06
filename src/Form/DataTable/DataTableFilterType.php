<?php

declare(strict_types=1);

namespace App\Form\DataTable;

use App\DTO\DataTable\DataTableFilterDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DataTableFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('keywords', SearchType::class, [
            'label' => 'Keywords',
            'required' => false
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DataTableFilterDTO::class,
            'csrf_protection' => false
        ]);
    }
}
