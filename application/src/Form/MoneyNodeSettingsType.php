<?php

declare(strict_types=1);

namespace App\Form;

use App\Form\CommonFormType;
use App\Model\MoneyNodeSettings;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MoneyNodeSettingsType extends CommonFormType
{
    protected function init(): void
    {
        $this->setTranslationModule(moduleName: 'money');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        for ($i = 0; $i < MoneyNodeSettings::GROUPS_MAX; $i++) {
            $builder
                ->add(child: 'name' . $i, type: TextType::class, options: [
                    'label' => $this->getLabelTrans(label: 'money_node_group_name') . ' #' . ($i + 1),
                    'required' => false,
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MoneyNodeSettings::class,
        ]);
    }
}
