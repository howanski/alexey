<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\Currency;
use App\Validator\MoneyTransferSplitCurrencyValiator;
use Symfony\Component\Validator\Constraint;

final class MoneyTransferSplitCurrency extends Constraint
{
    public string $message = 'app.validation.money_transfer_split.currency_mismatch';

    public ?Currency $currency;

    public function validatedBy(): string
    {
        return MoneyTransferSplitCurrencyValiator::class;
    }
}
