<?php

namespace Ekyna\Component\Commerce\Tests\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Tests\TestCase;

/**
 * Class PaymentValidatorTest
 * @package Ekyna\Component\Commerce\Tests\Bridge\Symfony\Validator\Constraints
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PaymentValidatorTest extends TestCase
{
    // TODO Following assertions using different payment amounts and states

    // Refund by outstanding method

    // Payment by outstanding method should NEVER add violation if customer has outstanding overflow and sale has outstanding limit

    // Payment by outstanding method should add violation if amount is greater than the sale outstanding limit (-accepted/expired)

    // Payment by outstanding method should add violation if amount is greater than the customer outstanding limit (+balance)

    // Refund by credit method should NEVER add violation

    // Payment by credit method should add violation if amount is greater than the customer balance
}
