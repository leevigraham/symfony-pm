<?php

declare(strict_types=1);

namespace App\Form\DataTable;

use App\DTO\DataTable\ProjectFilterDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('key', SearchType::class, [
            'label' => 'Key',
            'required' => false,
            'priority' => 10,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
    }

    public function getParent(): string
    {
        return DataTableFilterType::class;
    }
}
