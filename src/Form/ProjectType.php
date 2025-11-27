<?php

namespace App\Form;

use App\Entity\Organisation;
use App\Entity\Project;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('key')
            ->add('color', ColorType::class)
            ->add('organisation', EntityType::class, [
                'placeholder' => '',
                'class' => Organisation::class,
                'choice_label' => 'name',
            ])
            ->add('lead', EntityType::class, [
                'placeholder' => '',
                'class' => User::class,
                'choice_label' => 'displayName',
            ])
            ->add('defaultAssignee', EntityType::class, [
                'placeholder' => '',
                'class' => User::class,
                'choice_label' => 'displayName',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
