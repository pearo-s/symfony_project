<?php

namespace App\Form;

use App\Entity\WidgetSetting;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WidgetSettingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('frequency', ChoiceType::class, [
                'choices' => [
                    'minutely' => 'minutely',
                    'hourly' => 'hourly',
                    'daily' => 'daily',
                    'disable' => 'disable'
                ],
                'label' => 'Update frequency',
            ])
            ->add('is_shown', CheckboxType::class, ['required' => false])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WidgetSetting::class,
        ]);
    }
}
