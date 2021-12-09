<?php

declare(strict_types=1);

namespace App\Form;

use App\Form\CommonFormType;
use App\Service\NetworkUsageProviderSettings;
use App\Service\NetworkUsageService;
use App\Service\SimpleSettingsService;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class NetworkUsageProviderSettingsType extends CommonFormType
{
    protected function init(): void
    {
        $this->setTranslationModule(moduleName: 'network_usage');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(child: 'providerType', type: ChoiceType::class, options: [
                'label' => $this->getLabelTrans(label: 'provider_type'),
                'priority' => 0,
                'choices' => [
                    $this->getValueTrans(field: 'provider_type', value: 'off') =>
                    NetworkUsageService::NETWORK_USAGE_PROVIDER_NONE,
                    $this->getValueTrans(field: 'provider_type', value: 'hilink') =>
                    NetworkUsageService::NETWORK_USAGE_PROVIDER_HUAWEI
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add(child: 'address', type: TextType::class, options: [
                'label' => $this->getLabelTrans(label: 'address'),
                'priority' => -1,
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add(child: 'password', type: TextType::class, options: [
                'label' => $this->getLabelTrans(label: 'password'),
                'priority' => -2,
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add(child: 'showOnDashboard', type: ChoiceType::class, options: [
                'label' => $this->getLabelTrans(label: 'show_on_dashboard'),
                'priority' => -3,
                'choices' => [
                    $this->getValueTrans(field: 'show_on_dashboard', value: 'hide')
                    => SimpleSettingsService::UNIVERSAL_FALSE,
                    $this->getValueTrans(field: 'show_on_dashboard', value: 'show')
                    => SimpleSettingsService::UNIVERSAL_TRUTH,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NetworkUsageProviderSettings::class,
        ]);
    }
}
