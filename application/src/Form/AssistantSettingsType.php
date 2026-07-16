<?php

declare(strict_types=1);

namespace App\Form;

use App\Form\CommonFormType;
use App\Model\AssistantSettings;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class AssistantSettingsType extends CommonFormType
{
    protected function init(): void
    {
        $this->setTranslationModule(moduleName: 'assistant');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(child: 'baseUrl', type: TextType::class, options: [
                'label' => $this->getLabelTrans(label: 'base_url'),
                'priority' => 0,
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
                'help' => 'i.e. "http://my-inference-server.local:8080"',
            ])
            ->add(child: 'model', type: TextType::class, options: [
                'label' => $this->getLabelTrans(label: 'model'),
                'priority' => -1,
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
                'help' => 'i.e. "gemma-4-e4b"',
            ])
            ->add(child: 'apiKey', type: TextType::class, options: [
                'label' => $this->getLabelTrans(label: 'api_key'),
                'priority' => -2,
                'required' => false,
            ])
            ->add(child: 'systemMessage', type: TextareaType::class, options: [
                'label' => $this->getLabelTrans(label: 'system_message'),
                'priority' => -3,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AssistantSettings::class,
        ]);
    }
}
