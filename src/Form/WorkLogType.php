<?php

namespace App\Form;

use App\Entity\WorkLog;
use App\Entity\WorkItem;
use App\Enum\WorkLogStates;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkLogType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description')
            ->add('durationInSeconds')
            ->add('billable')
            ->add('billableDurationInSeconds')
//            ->add('state', EnumType::class, [
//                'class' => WorkLogStates::class,
//            ])
//            ->add('startedAt', null, [
//                'widget' => 'single_text',
//            ])
            ->add('workItem', EntityType::class, [
                'placeholder' => '',
                'class' => WorkItem::class,
                'choice_label' => 'title',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WorkLog::class,
        ]);
    }
}
