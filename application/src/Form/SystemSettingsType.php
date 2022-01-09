<?php

declare(strict_types=1);

namespace App\Form;

use App\Model\SystemSettings;
use App\Service\SimpleSettingsService;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SystemSettingsType extends CommonFormType
{
    protected function init(): void
    {
        $this->setTranslationModule(moduleName: 'settings');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(child: 'tunnelingAllowed', type: ChoiceType::class, options: [
                'label' => $this->getLabelTrans('tunneling_allowed'),
                'choices' => [
                    $this->getValueTrans(field: 'allow_tunneling', value: 'disallow')
                    => SimpleSettingsService::UNIVERSAL_FALSE,
                    $this->getValueTrans(field: 'allow_tunneling', value: 'allow')
                    => SimpleSettingsService::UNIVERSAL_TRUTH,
                ],
            ])
            ->add('cronJobs', CollectionType::class, [
                'entry_type' => CronJobType::class,
                'label' => $this->getLabelTrans('cron_jobs') . ': <br><br>',
                'label_html' => true,
                'allow_add' => false,
                'allow_delete' => false,
                'by_reference' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SystemSettings::class,
        ]);
    }
}
