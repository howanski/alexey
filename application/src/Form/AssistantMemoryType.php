<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\AssistantMemory;
use App\Form\CommonFormType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class AssistantMemoryType extends CommonFormType
{
    protected function init(): void
    {
        $this->setTranslationModule(moduleName: 'assistant');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(child: 'assistant', type: ChoiceType::class, options: [
                'label' => $this->getLabelTrans(label: 'model'),
                'priority' => 0,
                'required' => true,
                'choices' => $options['model_choices'],
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add(child: 'userTitle', type: TextType::class, options: [
                'label' => $this->getLabelTrans(label: 'title'),
                'priority' => -1,
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add(child: 'userContent', type: TextareaType::class, options: [
                'label' => $this->getLabelTrans(label: 'content'),
                'priority' => -2,
                'required' => true,
                'attr' => [
                    'class' => 'min-h-80 ' . CommonFormType::STANDARD_INPUT_CLASSES,
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
            'data_class' => AssistantMemory::class,
            'model_choices' => [],
        ]);
    }
}
