<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Contracts\Translation\TranslatorInterface;

class NetworkChartType extends AbstractType
{
    public const CHART_TYPE_TODAY = 'today';
    public const CHART_TYPE_WEEK = 'week';
    public const CHART_TYPE_BILLING_FRAME = 'currentFrame';
    public const CHART_TYPE_HOURS_TWO = 'twoHours';
    public const CHART_TYPE_MINUTES_TEN = 'tenMinutes';

    public const CHART_TYPES = [
        self::CHART_TYPE_TODAY,
        self::CHART_TYPE_WEEK,
        self::CHART_TYPE_BILLING_FRAME,
        self::CHART_TYPE_HOURS_TWO,
        self::CHART_TYPE_MINUTES_TEN,
    ];

    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('chartType', ChoiceType::class, [
                'choices' => [
                    $this->translator->trans('app.modules.network_usage.forms.values.chart_type.today') => self::CHART_TYPE_TODAY,
                    $this->translator->trans('app.modules.network_usage.forms.values.chart_type.last_week') => self::CHART_TYPE_WEEK,
                    $this->translator->trans('app.modules.network_usage.forms.values.chart_type.current_billing_frame') =>
                    self::CHART_TYPE_BILLING_FRAME,
                    $this->translator->trans('app.modules.network_usage.forms.values.chart_type.last_2_hours') => self::CHART_TYPE_HOURS_TWO,
                    $this->translator->trans('app.modules.network_usage.forms.values.chart_type.last_10_minutes') => self::CHART_TYPE_MINUTES_TEN,
                ], 'label' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
