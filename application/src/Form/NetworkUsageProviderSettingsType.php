<?php

declare(strict_types=1);

namespace App\Form;

use App\Service\NetworkUsageService;
use Symfony\Component\Form\AbstractType;
use App\Class\NetworkUsageProviderSettings;
use App\Service\SimpleSettingsService;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class NetworkUsageProviderSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('providerType', ChoiceType::class, [
                'choices' => [
                    'OFF' => NetworkUsageService::NETWORK_USAGE_PROVIDER_NONE,
                    'HUAWEI (HILINK)' => NetworkUsageService::NETWORK_USAGE_PROVIDER_HUAWEI
                ]
            ])
            ->add('address', TextType::class, ['required' => true])
            ->add('password', TextType::class, ['required' => true])
            ->add('showOnDashboard', ChoiceType::class, [
                'choices' => [
                    'HIDE' => SimpleSettingsService::UNIVERSAL_FALSE,
                    'SHOW' => SimpleSettingsService::UNIVERSAL_TRUTH
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NetworkUsageProviderSettings::class,
        ]);
    }
}
