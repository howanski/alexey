<?php

declare(strict_types=1);

namespace App\Form;

use App\Class\TransmissionSettings;
use App\Service\SimpleSettingsService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TransmissionSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('host', TextType::class, ['required' => true])
            ->add('user', TextType::class, ['required' => true])
            ->add('password', TextType::class, ['required' => true])
            ->add('targetSpeed', TextType::class, ['required' => true])
            ->add('algorithmAggression', TextType::class, ['required' => true])
            ->add('aggressionAdapt', ChoiceType::class, [
                'choices' => [
                    'Disabled' => SimpleSettingsService::UNIVERSAL_FALSE,
                    'Enabled' => SimpleSettingsService::UNIVERSAL_TRUTH,
                    'Increasing only' => TransmissionSettings::ADAPT_TYPE_UP_ONLY,
                ],
                'label' => 'Auto - adapt aggression'
            ])
            ->add('isActive', ChoiceType::class, [
                'choices' => [
                    'Disabled' => SimpleSettingsService::UNIVERSAL_FALSE,
                    'Enabled' => SimpleSettingsService::UNIVERSAL_TRUTH
                ],
                'label' => 'Throttling enabled'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TransmissionSettings::class,
        ]);
    }
}
