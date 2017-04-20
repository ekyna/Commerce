<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Quote\Repository;

use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Quote\Model\QuotePaymentInterface;
use Ekyna\Component\Commerce\Payment\Repository\PaymentRepositoryInterface;

/**
 * Interface QuotePaymentRepositoryInterface
 * @package Ekyna\Component\Commerce\Quote\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method QuotePaymentInterface findOneByKey(string $key)
 */
interface QuotePaymentRepositoryInterface extends PaymentRepositoryInterface
{
    public function findOneByQuoteAndKey(QuoteInterface $quote, string $key): ?QuotePaymentInterface;
}
