<?php

declare(strict_types=1);

namespace App\Form;

use Dom\Entity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\WeekType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('TextType', TextType::class, [
                'help' => 'Some help text',
            ])
            ->add('TextareaType', TextareaType::class)
            ->add('EmailType', EmailType::class)
            ->add('IntegerType', IntegerType::class)
            ->add('MoneyType', MoneyType::class)
            ->add('NumberType', NumberType::class)
            ->add('PasswordType', PasswordType::class)
            ->add('PercentType', PercentType::class)
            ->add('SearchType', SearchType::class)
            ->add('UrlType', UrlType::class)
            ->add('RangeType', RangeType::class)
            ->add('TelType', TelType::class)
            ->add('ColorType', ColorType::class)
            ->add('ChoiceType', ChoiceType::class, [
                'choices' => [
                    'Choice 1' => 'choice1',
                    'Choice 2' => 'choice2',
                    'Choice 3' => 'choice3',
                ],
            ])
            ->add('ChoiceType_Multiple', ChoiceType::class, [
                'multiple' => true,
                'choices' => [
                    'Choice 1' => 'choice1',
                    'Choice 2' => 'choice2',
                    'Choice 3' => 'choice3',
                ],
            ])
            ->add('ChoiceType_Expanded', ChoiceType::class, [
                'expanded' => true,
                'help' => 'Some help text',
                'choices' => [
                    'Choice 1' => 'choice1',
                    'Choice 2' => 'choice2',
                    'Choice 3' => 'choice3',
                ],
            ])
            ->add('ChoiceType_Expanded_Multiple', ChoiceType::class, [
                'expanded' => true,
                'multiple' => true,
                'help' => 'Some help text',
                'choices' => [
                    'Choice 1' => 'choice1',
                    'Choice 2' => 'choice2',
                    'Choice 3' => 'choice3',
                ],
            ])
//            ->add('EnumType', EnumType::class, [
//                'class' => \App\Enum\WorkItemPriority::class,
//            ])
//            ->add('EntityType', EntityType::class, [
//                'class' => \App\Entity\Project::class,
//            ])
//            ->add('CountryType', CountryType::class)
//            ->add('LanguageType', LanguageType::class)
//            ->add('LocaleType', LocaleType::class)
//            ->add('TimezoneType', TimezoneType::class)
//            ->add('CurrencyType', CurrencyType::class)

            ->add('DateType', DateType::class)
            ->add('DateType_Choice', DateType::class, [
                'widget' => 'choice',
            ])
//            ->add('DateIntervalType', DateIntervalType::class)
            ->add('DateTimeType', DateTimeType::class)
            ->add('DateTimeType_choice', DateTimeType::class, [
                'widget' => 'choice',
                'date_label' => 'Date'
            ])
            ->add('TimeType', TimeType::class)
            ->add('TimeType_Choice', TimeType::class, [
                'widget' => 'choice',
            ])
            ->add('BirthdayType', BirthdayType::class)
            ->add('WeekType', WeekType::class)
            ->add('CheckboxType', CheckboxType::class)
            ->add('FileType', FileType::class)
            ->add('RadioType', RadioType::class)
            ->add('ProjectType', ProjectType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
    }
}
