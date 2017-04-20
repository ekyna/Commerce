<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Quote\Model;

use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;

/**
 * Interface QuotePaymentInterface
 * @package Ekyna\Component\Commerce\Quote\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface QuotePaymentInterface extends PaymentInterface
{
    public function getQuote(): ?QuoteInterface;

    public function setQuote(?QuoteInterface $quote): QuotePaymentInterface;
}
