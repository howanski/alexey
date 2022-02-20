<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\MoneyNode;
use App\Form\CommonFormType;
use App\Entity\MoneyTransfer;
use App\Validator\MoneyTransferSplitCurrency;
use InvalidArgumentException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;

final class MoneyTransferSplitType extends CommonFormType
{
    protected function init(): void
    {
        $this->setTranslationModule(moduleName: 'money');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /**
         * @var MoneyTransfer
         */
        $splitSource = $options['source'];
        if (!($splitSource instanceof MoneyTransfer) || is_null($splitSource->getId())) {
            throw new InvalidArgumentException('Wrongly specified MoneyTransfer split source.');
        }

        $targetChoices = [];
        foreach ($options['money_node_choices'] as $groupName => $groupContents) {
            $targetGroup = [];
            /** @var MoneyNode $node */
            foreach ($groupContents as $node) {
                if ($node->canBeTransferTarget()) {
                    $targetGroup[] = $node;
                }
            }
            if (count($targetGroup) > 0) {
                $targetChoices[$groupName] = $targetGroup;
            }
        }
        ksort($targetChoices);

        $builder
            ->add(child: 'targetNodePrimary', type: EntityType::class, options: [
                'label' => $this->getLabelTrans(label: 'target_node'),
                'priority' => 0,
                'required' => true,
                'class' => MoneyNode::class,
                'choices' => [$options['source']->getTargetNode()],
                'choice_label' => 'name',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add(child: 'amountPrimary', type: MoneyType::class, options: [
                'label' => $this->getLabelTrans(label: 'amount'),
                'priority' => -1,
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new LessThan(value: $splitSource->getAmount()),
                ],
            ])
            ->add(child: 'targetNodeSecondary', type: EntityType::class, options: [
                'label' => $this->getLabelTrans(label: 'target_node_rest_of_split'),
                'priority' => -2,
                'required' => true,
                'class' => MoneyNode::class,
                'choices' => $targetChoices,
                'choice_label' => 'name',
                'constraints' => [
                    new NotBlank(),
                    new MoneyTransferSplitCurrency(['currency' => $options['source']->getTargetNode()->getCurrency()]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'source' => null,
            'money_node_choices' => [],
        ]);
    }
}
