<?php

namespace Ekyna\Component\Commerce\Quote\Model;

use Ekyna\Component\Commerce\Common\Model\SaleNotificationInterface;

/**
 * Interface QuoteNotificationInterface
 * @package Ekyna\Component\Commerce\Quote\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface QuoteNotificationInterface extends SaleNotificationInterface
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
     * @return $this|QuoteNotificationInterface
     */
    public function setQuote(QuoteInterface $quote = null);
}
