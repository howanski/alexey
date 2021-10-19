<?php

declare(strict_types=1);

namespace App\Form;

use App\Form\CommonFormType;
use App\Entity\NetworkMachine;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NetworkMachineType extends CommonFormType
{
    protected function init(): void
    {
        $this->setTranslationModule(moduleName: 'network_machines');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(child: 'uri', options: [
                'label' => $this->getLabelTrans(label: 'uri'),
                'priority' => 0,
            ])
            ->add(child: 'name', options: [
                'label' => $this->getLabelTrans(label: 'name'),
                'priority' => -1,

            ])
            ->add(child: 'macAddress', options: [
                'label' => $this->getLabelTrans(label: 'mac_address'),
                'priority' => -2,

            ])
            ->add(child: 'wakeDestination', options: [
                'label' => $this->getLabelTrans(label: 'wake_destination'),
                'priority' => -3,
            ])
            ->add(child: 'showOnDashboard', options: [
                'label' => $this->getLabelTrans(label: 'show_on_dashboard'),
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
