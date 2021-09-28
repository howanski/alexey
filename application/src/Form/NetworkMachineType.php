<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\NetworkMachine;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class NetworkMachineType extends AbstractType
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(child: 'uri', options: [
                'label' => $this->translator->trans('app.forms.labels.uri'),
                'priority' => 0,
            ])
            ->add(child: 'name', options: [
                'label' => $this->translator->trans('app.forms.labels.name'),
                'priority' => -1,

            ])
            ->add(child: 'macAddress', options: [
                'label' => $this->translator->trans('app.forms.labels.mac_address'),
                'priority' => -2,

            ])
            ->add(child: 'wakeDestination', options: [
                'label' => $this->translator->trans('app.forms.labels.wake_destination'),
                'priority' => -3,
            ])
            ->add(child: 'showOnDashboard', options: [
                'label' => $this->translator->trans('app.forms.labels.show_on_dashboard'),
                'priority' => -4,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NetworkMachine::class,
        ]);
    }
}
