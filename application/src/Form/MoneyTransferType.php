<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\MoneyNode;
use App\Entity\MoneyTransfer;
use App\Form\CommonFormType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class MoneyTransferType extends CommonFormType
{
    protected function init(): void
    {
        $this->setTranslationModule(moduleName: 'money');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(child: 'operationDateString', type: TextType::class, options: [
                'label' => $this->getLabelTrans(label: 'operation_date'),
                'priority' => 0,
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
                'attr' => [
                    'class' => 'datepicker-target ' . CommonFormType::STANDARD_INPUT_CLASSES,
                    'data-datepicker-locale' => $options['locale'],
                    'data-datepicker-format' => $options['date_format'],
                ],
            ])
            ->add(child: 'amount', type: MoneyType::class, options: [
                'label' => $this->getLabelTrans(label: 'amount'),
                'priority' => -1,
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add(child: 'exchangeRate', type: NumberType::class, options: [
                'label' => $this->getLabelTrans(label: 'exchange_rate'),
                'priority' => -2,
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add(child: 'sourceNode', type: EntityType::class, options: [
                'label' => $this->getLabelTrans(label: 'source_node'),
                'priority' => -3,
                'required' => true,
                'class' => MoneyNode::class,
                'choices' => $options['money_node_choices'],
                'choice_label' => 'name',

                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add(child: 'targetNode', type: EntityType::class, options: [
                'label' => $this->getLabelTrans(label: 'target_node'),
                'priority' => -4,
                'required' => true,
                'class' => MoneyNode::class,
                'choices' => $options['money_node_choices'],
                'choice_label' => 'name',

                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add(child: 'comment', type: TextareaType::class, options: [
                'label' => $this->getLabelTrans(label: 'comment'),
                'priority' => -4,
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MoneyTransfer::class,
            'money_node_choices' => [],
            'locale' => 'en',
            'date_format' => 'dd.mm.yyyy',
        ]);
    }
}
