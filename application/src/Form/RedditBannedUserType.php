<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\RedditBannedPoster;
use App\Form\CommonFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

final class RedditBannedUserType extends CommonFormType
{
    protected function init(): void
    {
        $this->setTranslationModule(moduleName: 'crawler');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(child: 'username', options: [
                'label' => $this->getLabelTrans('username'),
                'priority' => 0,
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RedditBannedPoster::class,
        ]);
    }
}
