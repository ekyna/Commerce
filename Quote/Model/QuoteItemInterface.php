<?php

namespace Ekyna\Component\Commerce\Quote\Model;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;

/**
 * Interface QuoteItemInterface
 * @package Ekyna\Component\Commerce\Quote\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method QuoteInterface getSale()
 */
interface QuoteItemInterface extends SaleItemInterface
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
     * @return $this|QuoteItemInterface
     */
    public function setQuote(QuoteInterface $quote = null);
}
