<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\FormBuilderInterface;

final class SystemSettingsType extends CommonFormType
{
    protected function init(): void
    {
        $this->setTranslationModule(moduleName: 'settings');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }
}
