<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\CronJob;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class CronJobType extends CommonFormType
{
    protected function init(): void
    {
        $this->setTranslationModule(moduleName: 'settings');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(child: 'isActive', options: [
            'label' => $this->getLabelTrans(label: 'is_cron_active'),
        ])->add(child: 'runEvery', type: IntegerType::class, options: [
            'label' => $this->getLabelTrans(label: 'run_cron_every'),
            'constraints' => [
                new NotBlank()
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CronJob::class,
        ]);
    }
}
