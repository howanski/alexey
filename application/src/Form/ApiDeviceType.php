<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\ApiDevice;
use App\Form\CommonFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

final class ApiDeviceType extends CommonFormType
{
    protected function init(): void
    {
        $this->setTranslationModule(moduleName: 'api');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(child: 'name', options: [
                'label' => $this->getLabelTrans(label: 'name'),
                'priority' => 0,
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ApiDevice::class,
        ]);
    }
}
