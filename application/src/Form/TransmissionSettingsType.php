<?php

declare(strict_types=1);

namespace App\Form;

use App\Form\CommonFormType;
use App\Model\TransmissionSettings;
use App\Service\SimpleSettingsService;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class TransmissionSettingsType extends CommonFormType
{
    private const AGGRESSION_ADAPT_CHOICES = [
        SimpleSettingsService::UNIVERSAL_FALSE,
        SimpleSettingsService::UNIVERSAL_TRUTH,
        TransmissionSettings::ADAPT_TYPE_UP_ONLY,
    ];

    protected function init(): void
    {
        $this->setTranslationModule(moduleName: 'network_usage');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choicesAggresionAdapt = [];
        foreach (self::AGGRESSION_ADAPT_CHOICES as $val) {
            $choicesAggresionAdapt[$this->getValueTrans(field: 'aggression_adapt', value: $val)] = $val;
        }
        ksort($choicesAggresionAdapt);

        $choicesThrottlingFrame = [];
        $frames = [
            TransmissionSettings::TARGET_SPEED_FRAME_FULL,
            TransmissionSettings::TARGET_SPEED_FRAME_DAY,
        ];
        foreach ($frames as $key) {
            $choicesThrottlingFrame[$this->getValueTrans(field: 'throttling_frame', value: $key)] = $key;
        }

        $builder
            ->add(child: 'host', type: TextType::class, options: [
                'label' => $this->getLabelTrans(label: 'host'),
                'priority' => 0,
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add(child: 'user', type: TextType::class, options: [
                'label' => $this->getLabelTrans(label: 'transmission_user'),
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
            ->add(child: 'targetSpeed', type: TextType::class, options: [
                'label' => $this->getLabelTrans(label: 'target_speed'),
                'priority' => -3,
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add(child: 'targetSpeedMax', type: IntegerType::class, options: [
                'label' => $this->getLabelTrans(label: 'top_speed'),
                'priority' => -4,
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add(child: 'algorithmAggression', type: TextType::class, options: [
                'label' => $this->getLabelTrans(label: 'algorithm_aggression'),
                'priority' => -5,
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add(child: 'aggressionAdapt', type: ChoiceType::class, options: [
                'choices' => $choicesAggresionAdapt,
                'label' => $this->getLabelTrans(label: 'algorithm_aggression_auto_adapt'),
                'priority' => -6,
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add(child: 'allowSpeedBump', type: ChoiceType::class, options: [
                'choices' => $this->falseTruthChoices(fieldName: 'allow_speed_bump'),
                'label' => $this->getLabelTrans(label: 'allow_target_speed_bumping'),
                'priority' => -7,
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('targetFrame', ChoiceType::class, [
                'choices' => $choicesThrottlingFrame,
                'label' => $this->getLabelTrans(label: 'throttling_frame'),
                'priority' => -8,
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('isActive', ChoiceType::class, [
                'choices' => $this->falseTruthChoices(fieldName: 'is_active'),
                'label' => $this->getLabelTrans(label: 'throttling_enabled'),
                'priority' => -9,
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
            ]);
    }

    private function falseTruthChoices(string $fieldName)
    {
        $choices = [];
        foreach ([SimpleSettingsService::UNIVERSAL_FALSE, SimpleSettingsService::UNIVERSAL_TRUTH] as $val) {
            $choices[$this->getValueTrans(field: $fieldName, value: $val)] = $val;
        }
        return $choices;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TransmissionSettings::class,
        ]);
    }
}
