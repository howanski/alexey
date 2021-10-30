<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserSettingsType extends CommonFormType
{
    public const LOCALES = [
        'en',
        'pl'
    ];

    protected function init(): void
    {
        $this->setTranslationModule(moduleName: 'settings');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $langChoices = [];
        foreach (self::LOCALES as $val) {
            $langChoices[$this->getValueTrans(field: 'locale', value: $val)] = $val;
        }
        $builder
            ->add(
                child: 'locale',
                type: ChoiceType::class,
                options: [
                    'required' => true,
                    'choices' => $langChoices,
                    'label' => $this->getLabelTrans(label: 'locale'),
                ],
            );
    }
}
