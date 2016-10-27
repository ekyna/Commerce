<?php

namespace Ekyna\Component\Commerce\Quote\Model;

use Ekyna\Component\Commerce\Common\Model\SaleAttachmentInterface;

/**
 * Interface QuoteAttachmentInterface
 * @package Ekyna\Component\Commerce\Quote\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface QuoteAttachmentInterface extends SaleAttachmentInterface
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
     * @param QuoteInterface|null $quote
     *
     * @return $this|QuoteAttachmentInterface
     */
    public function setQuote(QuoteInterface $quote = null);
}
