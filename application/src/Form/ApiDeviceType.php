<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\ApiDevice;
use App\Form\CommonFormType;
use App\Service\MobileApi;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
        $permChoices = [];
        foreach (MobileApi::API_PERMISSIONS as $perm) {
            $permChoices[$this->getValueTrans(field: 'permissions', value: $perm)] = $perm;
        }

        $builder
            ->add(child: 'name', options: [
                'label' => $this->getLabelTrans(label: 'name'),
                'priority' => 0,
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                ],
            ])
            ->add(child: 'permissions', type: ChoiceType::class, options: [
                'label' => $this->getLabelTrans(label: 'permissions'),
                'choices' => $permChoices,
                'multiple' => true,
                'expanded' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ApiDevice::class,
        ]);
    }
}
