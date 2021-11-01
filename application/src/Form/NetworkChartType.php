<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class NetworkChartType extends CommonFormType
{
    public const CHART_TYPE_BILLING_FRAME = 'current_billing_frame';
    public const CHART_TYPE_HOURS_2 = 'last_2_hours';
    public const CHART_TYPE_HOURS_48 = 'last_48_hours';
    public const CHART_TYPE_MINUTES_TEN = 'last_10_minutes';
    public const CHART_TYPE_TODAY = 'today';
    public const CHART_TYPE_WEEK = 'last_week';

    public const CHART_TYPES = [
        self::CHART_TYPE_BILLING_FRAME,
        self::CHART_TYPE_WEEK,
        self::CHART_TYPE_HOURS_48,
        self::CHART_TYPE_TODAY,
        self::CHART_TYPE_HOURS_2,
        self::CHART_TYPE_MINUTES_TEN,
    ];

    protected function init(): void
    {
        $this->setTranslationModule(moduleName: 'network_usage');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = [];
        foreach (self::CHART_TYPES as $chartType) {
            $choices[$this->getValueTrans(field: 'chart_type', value: $chartType)] = $chartType;
        }
        $builder
            ->add('chartType', ChoiceType::class, [
                'choices' => $choices,
                'label' => false,
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
