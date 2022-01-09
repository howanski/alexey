<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\RedditChannelGroup;
use App\Form\CommonFormType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

final class RedditChannelGroupType extends CommonFormType
{
    protected function init(): void
    {
        $this->setTranslationModule(moduleName: 'crawler');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(child: 'name', options: [
                'label' => $this->getLabelTrans('name'),
                'priority' => 0,
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                ],
            ])
            ->add(child: 'orderNumber', type: NumberType::class, options: [
                'label' => $this->getLabelTrans('orderNumber'),
                'priority' => -1,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RedditChannelGroup::class,
        ]);
    }
}
