<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

final class UserSettingsType extends CommonFormType
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
        ksort($langChoices);
        $builder
            ->add(
                child: 'locale',
                type: ChoiceType::class,
                options: [
                    'choices' => $langChoices,
                    'label' => $this->getLabelTrans(label: 'locale'),
                    'required' => true,
                    'constraints' => [
                        new NotBlank()
                    ],
                ],
            )
            ->add(
                child: 'email',
                type: EmailType::class,
                options: [
                    'label' => $this->getLabelTrans(label: 'your_email'),
                    'required' => false,
                    'constraints' => [
                        new Email(),
                    ],
                ],
            )
            ->add(
                child: 'redditUsername',
                type: TextType::class,
                options: [
                    'label' => $this->getLabelTrans(label: 'reddit_username'),
                    'required' => false,
                ],
            );
    }
}
