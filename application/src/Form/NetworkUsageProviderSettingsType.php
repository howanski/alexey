<?php

declare(strict_types=1);

namespace App\Form;

use App\Service\NetworkUsageService;
use App\Service\SimpleSettingsService;
use Symfony\Component\Form\AbstractType;
use App\Class\NetworkUsageProviderSettings;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class NetworkUsageProviderSettingsType extends AbstractType
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(child: 'providerType', type: ChoiceType::class, options: [
                'label' => $this->translator->trans('app.modules.network_usage.forms.labels.provider_type'),
                'priority' => 0,
                'choices' => [
                    $this->translator->trans('app.modules.network_usage.forms.values.off') =>
                    NetworkUsageService::NETWORK_USAGE_PROVIDER_NONE,
                    'Huawei (HiLink)' => NetworkUsageService::NETWORK_USAGE_PROVIDER_HUAWEI
                ]
            ])
            ->add(child: 'address', type: TextType::class, options: [
                'label' => $this->translator->trans('app.modules.network_usage.forms.labels.address'),
                'priority' => -1,
                'required' => true,
            ])
            ->add(child: 'password', type: TextType::class, options: [
                'label' => $this->translator->trans('app.modules.common.forms.labels.password'),
                'priority' => -2,
                'required' => true,
            ])
            ->add(child: 'showOnDashboard', type: ChoiceType::class, options: [
                'label' => $this->translator->trans('app.modules.common.forms.labels.show_on_dashboard'),
                'priority' => -3,
                'choices' => [
                    $this->translator->trans('app.modules.common.forms.values.hide') => SimpleSettingsService::UNIVERSAL_FALSE,
                    $this->translator->trans('app.modules.common.forms.values.show') => SimpleSettingsService::UNIVERSAL_TRUTH
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
