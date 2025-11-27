<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\User;
use App\Entity\WorkItem;
use App\Enum\WorkItemPriority;
use App\Enum\WorkItemStatus;
use App\Form\Autocomplete\ProjectAutocompleteType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('originalEstimateInSeconds')
            ->add('remainingEstimateInSeconds')
            ->add('timeSpentInSeconds')
            ->add('priority', EnumType::class,[
                'class' => WorkItemPriority::class,
            ])
            ->add('status', EnumType::class,[
                'class' => WorkItemStatus::class,
            ])
            ->add('project', EntityType::class, [
                'placeholder' => '',
                'class' => Project::class,
                'choice_label' => 'name',
//                'choice_lazy' => true,
            ])
            ->add('reporter', EntityType::class, [
                'placeholder' => '',
                'class' => User::class,
                'choice_label' => 'displayName',
//                'choice_lazy' => true,
            ])
            ->add('assignee', EntityType::class, [
                'placeholder' => '',
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
