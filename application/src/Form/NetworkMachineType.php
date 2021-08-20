<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\NetworkMachine;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NetworkMachineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uri')
            ->add('name')
            ->add('macAddress')
            ->add('wakeDestination')
            ->add('showOnDashboard')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NetworkMachine::class,
        ]);
    }
}
