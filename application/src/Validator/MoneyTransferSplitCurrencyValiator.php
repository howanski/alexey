<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\Currency;
use App\Entity\MoneyNode;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class MoneyTransferSplitCurrencyValiator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof MoneyTransferSplitCurrency) {
            throw new UnexpectedTypeException($constraint, MoneyTransferSplitCurrency::class);
        }

        $expectedCurrency = $constraint->currency;

        if ($value instanceof MoneyNode) {
            if (false === ($value->getCurrency() === $expectedCurrency)) {
                $this->context->buildViolation($constraint->message)
                    ->addViolation();
            }
        }
    }
}
