<?php

namespace Ekyna\Component\Commerce\Quote\Repository;

use Ekyna\Component\Commerce\Quote\Model\QuotePaymentInterface;
use Ekyna\Component\Commerce\Payment\Repository\PaymentRepositoryInterface;

/**
 * Interface QuotePaymentRepositoryInterface
 * @package Ekyna\Component\Commerce\Quote\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method QuotePaymentInterface|null findOneByKey($key)
 */
interface QuotePaymentRepositoryInterface extends PaymentRepositoryInterface
{
    /**
     * Creates a new quote payment instance.
     *
     * @return QuotePaymentInterface
     */
    public function createNew();
}
