<?php

namespace App\Form;

use App\Entity\MoneyNode;
use App\Form\CommonFormType;
use App\Entity\MoneyTransfer;
use App\Repository\MoneyNodeRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class MoneyTransferType extends CommonFormType
{
    protected function init(): void
    {
        $this->setTranslationModule(moduleName: 'money');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(child: 'operationDate', type: DateType::class, options: [
                'label' => $this->getLabelTrans(label: 'operation_date'),
                'priority' => 0,
                'required' => true,
                'constraints' => [
                    new NotBlank(),
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
                'query_builder' => function (MoneyNodeRepository $er) {
                    return $er->getQueryBuilderForForm();
                },
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
                'query_builder' => function (MoneyNodeRepository $er) {
                    return $er->getQueryBuilderForForm();
                },
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
        ]);
    }
}
