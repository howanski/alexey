<?php

declare(strict_types=1);

namespace App\Validator;

use App\Validator\MoneyTransferSplitCurrencyValiator;
use Symfony\Component\Validator\Constraint;

final class MoneyTransferSplitCurrency extends Constraint
{
    public $message = 'app.validation.money_transfer_split.currency_mismatch';

    public $currency;

    public function validatedBy(): string
    {
        return MoneyTransferSplitCurrencyValiator::class;
    }
}
