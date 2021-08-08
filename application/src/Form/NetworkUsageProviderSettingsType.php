<?php

namespace App\Form;

use App\Service\NetworkUsageService;
use Symfony\Component\Form\AbstractType;
use App\Class\NetworkUsageProviderSettings;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

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
                    'HIDE' => NetworkUsageService::DASHBOARD_HIDE,
                    'SHOW' => NetworkUsageService::DASHBOARD_SHOW
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
