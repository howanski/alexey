<?php

declare(strict_types=1);

namespace App\Form;

use App\Form\CommonFormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

final class StorageItemRemoveQuantityType extends CommonFormType
{
    protected function init(): void
    {
        $this->setTranslationModule(moduleName: 'storage');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
            'max' => 0,
        ]);
    }
}
