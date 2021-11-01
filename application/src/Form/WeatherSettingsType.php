<?php

declare(strict_types=1);

namespace App\Form;

use App\Form\CommonFormType;
use App\Class\WeatherSettings;
use App\Service\SimpleSettingsService;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class WeatherSettingsType extends CommonFormType
{
    protected function init(): void
    {
        $this->setTranslationModule(moduleName: 'weather');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(child: 'latitude', type: TextType::class, options: [
                'label' => $this->getLabelTrans(label: 'latitude'),
                'priority' => 0,
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
                'help' => $this->getHelpTrans(field: 'latitude'),
            ])
            ->add(child: 'longitude', type: TextType::class, options: [
                'label' => $this->getLabelTrans(label: 'longitude'),
                'priority' => -1,
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
                'help' => $this->getHelpTrans(field: 'longitude'),
            ])
            ->add(child: 'apiKey', type: TextType::class, options: [
                'label' => $this->getLabelTrans(label: 'api_key'),
                'priority' => -2,
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
                'help' => $this->getHelpTrans(field: 'api_key'),
                'help_html' => true,
            ])
            ->add(child: 'showOnDashboard', type: ChoiceType::class, options: [
                'choices' => [
                    $this->getValueTrans(field: 'show_on_dashboard', value: 'hide')
                    => SimpleSettingsService::UNIVERSAL_FALSE,
                    $this->getValueTrans(field: 'show_on_dashboard', value: 'show')
                    => SimpleSettingsService::UNIVERSAL_TRUTH,
                ],
                'label' => $this->getLabelTrans(label: 'show_on_dashboard'),
                'priority' => -3,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WeatherSettings::class,
        ]);
    }
}
