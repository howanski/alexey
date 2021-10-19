<?php

declare(strict_types=1);

namespace App\Form;

use App\Class\WeatherSettings;
use App\Service\SimpleSettingsService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class WeatherSettingsType extends AbstractType
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(child: 'latitude', type: TextType::class, options: [
                'label' => $this->translator->trans('app.modules.weather.forms.labels.latitude'),
                'priority' => 0,
                'required' => true,
            ])
            ->add(child: 'longitude', type: TextType::class, options: [
                'label' => $this->translator->trans('app.modules.weather.forms.labels.longitude'),
                'priority' => -1,
                'required' => true,
            ])
            ->add(child: 'apiKey', type: TextType::class, options: [
                'label' => $this->translator->trans('app.modules.weather.forms.labels.api_key'),
                'priority' => -2,
                'required' => true,
            ])
            ->add(child: 'showOnDashboard', type: ChoiceType::class, options: [
                'label' => $this->translator->trans('app.modules.common.forms.labels.show_on_dashboard'),
                'choices' => [
                    $this->translator->trans('app.modules.common.forms.values.show_on_dashboard.hide') => SimpleSettingsService::UNIVERSAL_FALSE,
                    $this->translator->trans('app.modules.common.forms.values.show_on_dashboard.show') => SimpleSettingsService::UNIVERSAL_TRUTH,
                ],
                'priority' => -3,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WeatherSettings::class,
        ]);
    }
}
