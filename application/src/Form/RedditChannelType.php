<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\RedditChannel;
use App\Form\CommonFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

final class RedditChannelType extends CommonFormType
{
    protected function init(): void
    {
        $this->setTranslationModule(moduleName: 'crawler');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(child: 'name', options: [
                'label' => '/r/',
                'priority' => 0,
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                ],
            ])
            ->add(child: 'nsfw', options: [
                'label' => 'nsfw',
                'priority' => -1,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RedditChannel::class,
        ]);
    }
}
