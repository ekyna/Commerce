<?php

namespace Ekyna\Component\Commerce\Quote\Model;

use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;

/**
 * Interface QuotePaymentInterface
 * @package Ekyna\Component\Commerce\Quote\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface QuotePaymentInterface extends PaymentInterface
{
    /**
     * Returns the quote.
     *
     * @return QuoteInterface
     */
    public function getQuote();

    /**
     * Sets the quote.
     *
     * @param QuoteInterface $quote
     *
     * @return $this|QuotePaymentInterface
     */
    public function setQuote(QuoteInterface $quote = null);
}
