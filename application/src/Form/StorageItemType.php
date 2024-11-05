<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\StorageItem;
use App\Entity\StorageSpace;
use App\Form\CommonFormType;
use App\Repository\StorageSpaceRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

final class StorageItemType extends CommonFormType
{
    protected function init(): void
    {
        $this->setTranslationModule(moduleName: 'storage');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $unitChoices = [];
        foreach (StorageItem::VALID_UNITS as $unitId) {
            $code = StorageItem::VALID_UNITS_TRANS_CODES[$unitId];
            $unitChoices[$this->getValueTrans(field: 'unit_of_measure', value: $code)] = $unitId;
        }
        ksort($unitChoices);

        $user = $options['user'];

        $builder
            ->add(child: 'name', options: [
                'label' => $this->getLabelTrans('name'),
                'priority' => 0,
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                ],
            ])
            ->add(child: 'unitOfMeasure', type: ChoiceType::class, options: [
                'choices' => $unitChoices,
                'required' => true,
                'label' => $this->getLabelTrans('unit'),
                'priority' => -1,
            ])
            ->add(child: 'minimalQuantity', type: IntegerType::class, options: [
                'required' => true,
                'label' => $this->getLabelTrans(label: 'minimal_quantity'),
                'help' => $this->getHelpTrans(field: 'minimal_quantity'),
                'priority' => -2,
                'attr' => [
                    'min' => 0
                ],
                'constraints' => [
                    new GreaterThanOrEqual(value: 0)
                ],
            ]);

        if (true === $options['isNew']) {
            $builder
                ->add(child: 'storageSpace', type: EntityType::class, options: [
                    'mapped' => false,
                    'required' => true,
                    'class' => StorageSpace::class,
                    'label' => $this->getLabelTrans('storage_space'),
                    'choice_label' => 'name',
                    'priority' => -3,
                    'query_builder' => function (StorageSpaceRepository $er) use ($user) {
                        return $er->getFindByUserBuilder($user);
                    },
                ])
                ->add(child: 'currentQuantity', type: IntegerType::class, options: [
                    'mapped' => false,
                    'required' => true,
                    'label' => $this->getLabelTrans(label: 'current_quantity'),
                    'priority' => -4,
                    'attr' => [
                        'min' => 0
                    ],
                    'constraints' => [
                        new GreaterThanOrEqual(value: 0)
                    ],
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StorageItem::class,
            'isNew' => false,
            'user' => null,
        ]);
    }
}
