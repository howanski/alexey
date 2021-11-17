<?php

declare(strict_types=1);

namespace App\Form;

use BadMethodCallException;
use App\Service\AlexeyTranslator;
use Symfony\Component\Form\AbstractType;

abstract class CommonFormType extends AbstractType
{
    private $translationModule = null;

    public function __construct(
        private AlexeyTranslator $translator,
    ) {
        $this->init();
    }

    protected function init(): void
    {
    }

    protected function getLabelTrans(string $label): string
    {
        if (is_null($this->translationModule)) {
            throw new BadMethodCallException('setTranslationModule function not called!');
        }

        return $this->translator->translateFormLabel(label: $label, module: $this->translationModule);
    }

    protected function getValueTrans(string $field, string $value): string
    {
        if (is_null($this->translationModule)) {
            throw new BadMethodCallException('setTranslationModule function not called!');
        }

        return $this->translator->translateFormValue(
            value: $value,
            field: $field,
            module: $this->translationModule,
        );
    }

    protected function getHelpTrans(string $field): string
    {
        if (is_null($this->translationModule)) {
            throw new BadMethodCallException('setTranslationModule function not called!');
        }

        return $this->translator->translateFormHelp(
            field: $field,
            module: $this->translationModule,
        );
    }

    protected function setTranslationModule(string $moduleName): void
    {
        $this->translationModule = $moduleName;
    }
}
