<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\RedditChannel;
use App\Entity\RedditChannelGroup;
use App\Form\CommonFormType;
use App\Repository\RedditChannelGroupRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];
        $builder
            ->add(child: 'name', options: [
                'label' => '/r/',
                'priority' => 0,
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                ],
                'disabled' => !$options['isNew'],
            ])
            ->add(child: 'channelGroup', type: EntityType::class, options: [
                'class' => RedditChannelGroup::class,
                'label' => $this->getLabelTrans('channelGroup'),
                'choice_label' => 'name',
                'priority' => -1,
                'query_builder' => function (RedditChannelGroupRepository $er) use ($user) {
                    return $er->getMineBuilder($user);
                },
                'expanded' => false,
                'required' => false,
                'placeholder' => '---'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RedditChannel::class,
            'user' => null,
            'isNew' => false,
        ]);
    }
}
