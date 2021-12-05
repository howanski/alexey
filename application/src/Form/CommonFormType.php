<?php

declare(strict_types=1);

namespace App\Form;

use BadMethodCallException;
use App\Service\AlexeyTranslator;
use Symfony\Component\Form\AbstractType;

abstract class CommonFormType extends AbstractType
{
    public const STANDARD_INPUT_CLASSES = '' .
        'border-0 px-3 py-3 placeholder-gray-400 text-gray-700 bg-white rounded text-sm shadow' .
        ' focus:outline-none focus:ring w-full';

    private $translationModule = null;

    public function __construct(
        private AlexeyTranslator $translator,
    ) {
        $this->init();
    }

    abstract protected function init();

    final protected function getLabelTrans(string $label): string
    {
        if (is_null($this->translationModule)) {
            throw new BadMethodCallException('setTranslationModule function not called!');
        }

        return $this->translator->translateFormLabel(label: $label, module: $this->translationModule);
    }

    final protected function getValueTrans(string $field, string $value): string
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

    final protected function getHelpTrans(string $field): string
    {
        if (is_null($this->translationModule)) {
            throw new BadMethodCallException('setTranslationModule function not called!');
        }

        return $this->translator->translateFormHelp(
            field: $field,
            module: $this->translationModule,
        );
    }

    final protected function setTranslationModule(string $moduleName): void
    {
        $this->translationModule = $moduleName;
    }
}
