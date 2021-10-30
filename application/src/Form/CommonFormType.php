<?php

declare(strict_types=1);

namespace App\Form;

use BadMethodCallException;
use App\Service\AlexeyTranslator;
use Symfony\Component\Form\AbstractType;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Intl\Exception\MissingResourceException;

class CommonFormType extends AbstractType
{
    private $translationModule = null;
    private $debugMode = false;

    public function __construct(
        private TranslatorInterface $translator,
        // TODO: Use AlexeyTranslator
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

        $translationId = strtolower('app.modules.'
            . $this->translationModule
            . '.forms.labels.'
            . $label);

        if ($this->isTranslated($translationId)) {
            return $this->trans($translationId);
        }

        $translationCommonId = strtolower('app.modules.'
            . AlexeyTranslator::DEFAULT_TRANSLATION_MODULE
            . '.forms.labels.'
            . $label);

        if ($this->isTranslated($translationCommonId)) {
            return $this->trans($translationCommonId);
        }
        if (true === $this->debugMode) {
            dump($translationId);
            dump($translationCommonId);
            die('Both translations not found');
        }
        throw new MissingResourceException(
            'Label ' . $label . ' not translated in module '
                . $this->translationModule . ' !'
        );
    }

    protected function getValueTrans(string $field, string $value): string
    {
        if (is_null($this->translationModule)) {
            throw new BadMethodCallException('setTranslationModule function not called!');
        }

        $translationId = strtolower('app.modules.'
            . $this->translationModule
            . '.forms.values.' . $field . '.'
            . $value);

        if ($this->isTranslated($translationId)) {
            return $this->trans($translationId);
        }

        $translationCommonId = strtolower('app.modules.'
            . AlexeyTranslator::DEFAULT_TRANSLATION_MODULE
            . '.forms.values.' . $field . '.'
            . $value);


        if ($this->isTranslated($translationCommonId)) {
            return $this->trans($translationCommonId);
        }
        if (true === $this->debugMode) {
            dump($translationId);
            dump($translationCommonId);
            die('Both translations not found');
        }
        throw new MissingResourceException(
            message: 'Value ' . $value . ' for field ' . $field
                . ' not translated in module ' . $this->translationModule . ' !',
        );
    }

    protected function setTranslationModule(string $moduleName): void
    {
        $this->translationModule = $moduleName;
    }

    private function trans(string $translationId): string
    {
        return $this->translator->trans($translationId);
    }

    private function isTranslated(string $string): bool
    {
        $translated = $this->translator->trans($string);
        return !($translated === $string);
    }
}
