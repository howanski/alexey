<?php

declare(strict_types=1);

namespace App\Service;

use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Intl\Exception\MissingResourceException;

class AlexeyTranslator extends AbstractExtension
{

    public const DEFAULT_TRANSLATION_MODULE = 'common';

    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('localised', [$this, 'translateString']),
            new TwigFilter('localisedTime', [$this, 'translateTime']),
            new TwigFilter('localisedFormLabel', [$this, 'translateFormLabel']),
            new TwigFilter('localisedFormValue', [$this, 'translateFormValue']),
        ];
    }

    public function translateTime(
        string $value,
        string $timeUnit,
        string $type = 'default',
    ): string {
        $translationId = strtolower('app.time.' . $timeUnit . '.' . $type . '.' . $value);
        if ($this->isTranslated($translationId)) {
            return $this->translator->trans($translationId);
        }
        throw new MissingResourceException(
            message: 'Time translation ' . $translationId . ' not found!',
        );
    }

    public function translateFlash(string $translationId, string $module = self::DEFAULT_TRANSLATION_MODULE): string
    {
        // TODO: refactor to common methods
        $fullId = 'app.modules.' . $module . '.flashes.' . $translationId;
        if ($this->isTranslated($fullId)) {
            return $this->translator->trans($fullId);
        }
        $commonId = 'app.modules.' . self::DEFAULT_TRANSLATION_MODULE . '.flashes.' . $translationId;
        if ($this->isTranslated($commonId)) {
            return $this->translator->trans($commonId);
        }
        throw new MissingResourceException(
            message: 'Flash ' . $translationId
                . ' not translated in module ' . $module . ' !',
        );
    }

    public function translateString(string $translationId, string $module = self::DEFAULT_TRANSLATION_MODULE): string
    {
        $fullId = 'app.modules.' . $module . '.strings.' . $translationId;
        if ($this->isTranslated($fullId)) {
            return $this->translator->trans($fullId);
        }
        $commonId = 'app.modules.' . self::DEFAULT_TRANSLATION_MODULE . '.strings.' . $translationId;
        if ($this->isTranslated($commonId)) {
            return $this->translator->trans($commonId);
        }
        throw new MissingResourceException(
            message: 'String ' . $translationId
                . ' not translated in module ' . $module . ' !',
        );
    }

    public function translateFormLabel(string $label, string $module): string
    {

        $translationId = strtolower('app.modules.'
            . $module
            . '.forms.labels.'
            . $label);

        if ($this->isTranslated($translationId)) {
            return $this->translator->trans($translationId);
        }

        $translationCommonId = strtolower('app.modules.'
            . self::DEFAULT_TRANSLATION_MODULE
            . '.forms.labels.'
            . $label);

        if ($this->isTranslated($translationCommonId)) {
            return $this->translator->trans($translationCommonId);
        }

        throw new MissingResourceException(
            'Label ' . $label . ' not translated in module '
                . $module . ' !'
        );
    }

    public function translateFormValue(string $value, string $field, string $module): string
    {
        $translationId = strtolower('app.modules.'
            . $module
            . '.forms.values.' . $field . '.'
            . $value);

        if ($this->isTranslated($translationId)) {
            return $this->translator->trans($translationId);
        }

        $translationCommonId = strtolower('app.modules.'
            . self::DEFAULT_TRANSLATION_MODULE
            . '.forms.values.' . $field . '.'
            . $value);


        if ($this->isTranslated($translationCommonId)) {
            return $this->translator->trans($translationCommonId);
        }
        throw new MissingResourceException(
            message: 'Value ' . $value . ' for field ' . $field
                . ' not translated in module ' . $module . ' !',
        );
    }

    private function isTranslated(string $string): bool
    {
        $translated = $this->translator->trans($string);
        return !($translated === $string);
    }
}
