<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserSettingsType extends AbstractType
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                child: 'locale',
                type: ChoiceType::class,
                options: [
                    'required' => true,
                    'choices' => [
                        $this->translator->trans('app.forms.values.locale.english') => 'en',
                        $this->translator->trans('app.forms.values.locale.polish') => 'pl',
                    ],
                    'label' => $this->translator->trans('app.forms.labels.locale'),
                ],
            );
    }
}
