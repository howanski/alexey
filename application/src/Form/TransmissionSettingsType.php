<?php

declare(strict_types=1);

namespace App\Form;

use App\Class\TransmissionSettings;
use App\Service\SimpleSettingsService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TransmissionSettingsType extends AbstractType
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(child: 'host', type: TextType::class, options: [
                'label' => $this->translator->trans('app.forms.labels.host'),
                'priority' => 0,
                'required' => true,
            ])
            ->add(child: 'user', type: TextType::class, options: [
                'label' => $this->translator->trans('app.forms.labels.user'),
                'priority' => -1,
                'required' => true,
            ])
            ->add(child: 'password', type: TextType::class, options: [
                'label' => $this->translator->trans('app.forms.labels.password'),
                'priority' => -2,
                'required' => true,
            ])
            ->add(child: 'targetSpeed', type: TextType::class, options: [
                'label' => $this->translator->trans('app.forms.labels.target_speed'),
                'priority' => -3,
                'required' => true,
            ])
            ->add(child: 'algorithmAggression', type: TextType::class, options: [
                'label' => $this->translator->trans('app.forms.labels.algorithm_aggression'),
                'priority' => -4,
                'required' => true,
            ])
            ->add(child: 'aggressionAdapt', type: ChoiceType::class, options: [
                'choices' => [
                    $this->translator->trans('app.forms.values.disabled') => SimpleSettingsService::UNIVERSAL_FALSE,
                    $this->translator->trans('app.forms.values.enabled') => SimpleSettingsService::UNIVERSAL_TRUTH,
                    $this->translator->trans('app.forms.values.increasing') => TransmissionSettings::ADAPT_TYPE_UP_ONLY,
                ],
                'label' => $this->translator->trans('app.forms.labels.algorithm_aggression_auto_adapt'),
                'priority' => -5,
                'required' => true,
            ])
            ->add(child: 'allowSpeedBump', type: ChoiceType::class, options: [
                'choices' => [
                    $this->translator->trans('app.forms.values.disabled') => SimpleSettingsService::UNIVERSAL_FALSE,
                    $this->translator->trans('app.forms.values.enabled') => SimpleSettingsService::UNIVERSAL_TRUTH
                ],
                'label' => $this->translator->trans('app.forms.labels.allow_target_speed_bumping'),
                'priority' => -6,
            ])
            ->add('isActive', ChoiceType::class, [
                'choices' => [
                    $this->translator->trans('app.forms.values.disabled') => SimpleSettingsService::UNIVERSAL_FALSE,
                    $this->translator->trans('app.forms.values.enabled') => SimpleSettingsService::UNIVERSAL_TRUTH
                ],
                'label' => $this->translator->trans('app.forms.labels.throttling_enabled'),
                'priority' => -7,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TransmissionSettings::class,
        ]);
    }
}
