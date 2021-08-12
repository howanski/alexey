<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use function Symfony\Component\String\s;

class NetworkChartType extends AbstractType
{
    public const CHART_TYPE_TODAY = 'today';
    public const CHART_TYPE_WEEK = 'week';
    public const CHART_TYPE_MONTH = 'month';
    public const CHART_TYPE_BILLING_FRAME = 'currentFrame';

    public const CHART_TYPES = [
        self::CHART_TYPE_TODAY,
        self::CHART_TYPE_WEEK,
        self::CHART_TYPE_MONTH,
        self::CHART_TYPE_BILLING_FRAME,
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('chartType', ChoiceType::class, [
                'choices' => [
                    'Today' => self::CHART_TYPE_TODAY,
                    'Last week' => self::CHART_TYPE_WEEK,
                    'Last month' => self::CHART_TYPE_MONTH,
                    'Current billing frame' => self::CHART_TYPE_BILLING_FRAME,
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
