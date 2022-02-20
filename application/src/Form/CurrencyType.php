<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Currency;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

final class CurrencyType extends CommonFormType
{
    protected function init(): void
    {
        $this->setTranslationModule(moduleName: 'money');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(child: 'code', options: [
                'label' => $this->getLabelTrans(label: 'currency_code'),
                'priority' => 0,
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(exactly: 3),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Currency::class,
        ]);
    }
}
