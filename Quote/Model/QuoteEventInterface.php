<?php

namespace Ekyna\Component\Commerce\Quote\Model;

/**
 * Interface QuoteEventInterface
 * @package Ekyna\Component\Commerce\Quote\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface QuoteEventInterface
{
    /**
     * Returns the quote.
     *
     * @return QuoteInterface
     */
    public function getQuote();
}
