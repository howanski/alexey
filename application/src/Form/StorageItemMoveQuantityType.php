<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\StorageSpace;
use App\Form\CommonFormType;
use App\Repository\StorageSpaceRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

final class StorageItemMoveQuantityType extends CommonFormType
{
    protected function init(): void
    {
        $this->setTranslationModule(moduleName: 'storage');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $user = $options['user'];

        $builder
            ->add(child: 'quantity', type: IntegerType::class, options: [
                'required' => true,
                'label' => $this->getLabelTrans(label: 'quantity'),
                'priority' => 0,
                'attr' => [
                    'min' => 0,
                    'max' => $options['max']
                ],
                'constraints' => [
                    new GreaterThanOrEqual(value: 0)
                ],
            ])
            ->add(child: 'storageSpace', type: EntityType::class, options: [
                'mapped' => false,
                'required' => true,
                'class' => StorageSpace::class,
                'label' => $this->getLabelTrans('storage_space'),
                'choice_label' => 'name',
                'priority' => -1,
                'query_builder' => function (StorageSpaceRepository $er) use ($user) {
                    return $er->getFindByUserBuilder($user);
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
            'user' => null,
            'max' => 0,
        ]);
    }
}
