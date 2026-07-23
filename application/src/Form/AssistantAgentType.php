<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\AssistantRecurringMessage;
use App\Form\CommonFormType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class AssistantAgentType extends CommonFormType
{
    protected function init(): void
    {
        $this->setTranslationModule(moduleName: 'assistant');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(child: 'name', type: TextType::class, options: [
                'label' => $this->getLabelTrans(label: 'agent_name'),
                'priority' => 0,
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add(child: 'model', type: TextType::class, options: [
                'label' => $this->getLabelTrans(label: 'model'),
                'priority' => -1,
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
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
            'data_class' => AssistantRecurringMessage::class,
        ]);
    }
}
