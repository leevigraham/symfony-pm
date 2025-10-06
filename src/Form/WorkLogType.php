<?php

namespace App\Form;

use App\Entity\WorkLog;
use App\Entity\WorkItem;
use App\Enum\WorkLogStates;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkLogType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('workItem', EntityType::class, [
                'class' => WorkItem::class,
                'choice_label' => 'title',
            ])
            ->add('description')
            ->add('state', EnumType::class, [
                'class' => WorkLogStates::class,
            ])
            ->add('startedAt', null, [
                'widget' => 'single_text',
            ])
//            ->add('durationInSeconds')
            ->add('billable')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WorkLog::class,
        ]);
    }
}
