<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\User;
use App\Entity\WorkItem;
use App\Form\Autocomplete\ProjectAutocompleteType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
//            ->add('title')
//            ->add('description')
            ->add('project', EntityType::class, [
                'class' => Project::class,
                'choice_label' => 'name',
//                'choice_lazy' => true,
            ])
//            ->add('project', ProjectAutocompleteType::class)
            ->add('reporter', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'displayName',
//                'choice_lazy' => true,
            ])
            ->add('assignee', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'displayName',
//                'choice_lazy' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WorkItem::class,
        ]);
    }
}
