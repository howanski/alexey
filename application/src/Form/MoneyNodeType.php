<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Currency;
use App\Entity\MoneyNode;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

final class MoneyNodeType extends CommonFormType
{
    protected function init(): void
    {
        $this->setTranslationModule(moduleName: 'money');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $typeChoices = [];
        foreach (MoneyNode::NODE_TYPES as $typeId) {
            $code = MoneyNode::NODE_TYPE_CODES[$typeId];
            $typeChoices[$this->getValueTrans(field: 'node_type', value: $code)] = $typeId;
        }
        ksort($typeChoices);

        $builder
            ->add(child: 'name', options: [
                'label' => $this->getLabelTrans(label: 'name'),
                'priority' => 0,
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add(child: 'nodeType', type: ChoiceType::class, options: [
                'label' => $this->getLabelTrans(label: 'node_type'),
                'priority' => -1,
                'choices' => $typeChoices,
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add(child: 'nodeGroup', type: ChoiceType::class, options: [
                'label' => $this->getLabelTrans(label: 'node_group'),
                'priority' => -2,
                'choices' => $options['node_group_choices'],
                'required' => true,
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add(child: 'notes', type: TextareaType::class, options: [
                'label' => $this->getLabelTrans(label: 'notes'),
                'priority' => -3,
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add(child: 'currency', type: EntityType::class, options: [
                'label' => $this->getLabelTrans(label: 'currency'),
                'class' => Currency::class,
                'priority' => -4,
                'required' => true,
                'choice_label' => 'code',
                'choices' => $options['currencies'],
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add(child: 'selectable', type: CheckboxType::class, options: [
                'label' => $this->getLabelTrans(label: 'selectable'),
                'priority' => -5,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MoneyNode::class,
            'node_group_choices' => [
                '---' => 0,
            ],
            'currencies' => [],
        ]);
    }
}
