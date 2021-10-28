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
        ];
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

    private function isTranslated(string $string): bool
    {
        $translated = $this->translator->trans($string);
        return !($translated === $string);
    }
}
