<?php

declare(strict_types=1);

namespace App\Form;

use App\Form\CommonFormType;
use App\Model\AssistantMessageDTO;
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
            ->add(child: 'message', type: TextareaType::class, options: [
                'label' => $this->getLabelTrans(label: 'message'),
                'priority' => -1,
                'required' => true,
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
