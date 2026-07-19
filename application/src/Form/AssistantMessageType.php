<?php

declare(strict_types=1);

namespace App\Form;

use App\Form\CommonFormType;
use App\Model\AssistantMessageDTO;
use App\Service\AssistantService;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class AssistantMessageType extends CommonFormType
{
    protected function init(): void
    {
        $this->setTranslationModule(moduleName: 'assistant');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $toolChoices = [];
        foreach (AssistantService::TOOLS_AVAILABLE as $toolName) {
            $toolChoices[$this->getValueTrans(field: 'tools', value: $toolName)] = $toolName;
        }
        ksort($toolChoices);

        $builder
            ->add(child: 'model', type: ChoiceType::class, options: [
                'label' => $this->getLabelTrans(label: 'model'),
                'priority' => 0,
                'required' => true,
                'choices' => $options['model_choices'],
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add(child: 'tools', type: ChoiceType::class, options: [
                'label' => $this->getLabelTrans(label: 'tools'),
                'multiple' => true,
                'expanded' => true,
                'priority' => -1,
                'required' => false,
                'choices' => $toolChoices,
            ])
            ->add(child: 'message', type: TextareaType::class, options: [
                'label' => $this->getLabelTrans(label: 'message'),
                'priority' => -2,
                'required' => true,
                'attr' => [
                    'class' => 'min-h-120 ' . CommonFormType::STANDARD_INPUT_CLASSES,
                ],
                'constraints' => [
                    new NotBlank()
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AssistantMessageDTO::class,
            'model_choices' => [],
        ]);
    }
}
