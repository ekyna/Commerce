<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Quote\Repository;

use Ekyna\Component\Commerce\Payment\Repository\PaymentRepositoryInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Quote\Model\QuotePaymentInterface;

/**
 * Interface QuotePaymentRepositoryInterface
 * @package Ekyna\Component\Commerce\Quote\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements PaymentRepositoryInterface<QuotePaymentInterface>
 */
interface QuotePaymentRepositoryInterface extends PaymentRepositoryInterface
{
    public function findOneByQuoteAndKey(QuoteInterface $quote, string $key): ?QuotePaymentInterface;
}
