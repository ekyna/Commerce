<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\Resolver;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;

/**
 * Class PaymentData
 * @package Ekyna\Component\Commerce\Invoice\Resolver
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PaymentData
{
    public PaymentInterface $payment;
    public bool             $refund;
    public Decimal          $amount;
    public Decimal          $realAmount;

    public function __construct(PaymentInterface $payment, bool $credit, Decimal $amount, Decimal $realAmount)
    {
        $this->payment = $payment;
        $this->refund = $credit;
        $this->amount = $amount;
        $this->realAmount = $realAmount;
    }
}
