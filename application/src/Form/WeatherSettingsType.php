<?php

declare(strict_types=1);

namespace App\Form;

use App\Class\WeatherSettings;
use App\Service\SimpleSettingsService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class WeatherSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('latitude', TextType::class, ['required' => true])
            ->add('longitude', TextType::class, ['required' => true])
            ->add('apiKey', TextType::class, ['required' => true])
            ->add('showOnDashboard', ChoiceType::class, [
                'choices' => [
                    'HIDE' => SimpleSettingsService::UNIVERSAL_FALSE,
                    'SHOW' => SimpleSettingsService::UNIVERSAL_TRUTH
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WeatherSettings::class,
        ]);
    }
}
