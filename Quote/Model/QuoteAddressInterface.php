<?php

namespace Ekyna\Component\Commerce\Quote\Model;

use Ekyna\Component\Commerce\Common\Model\SaleAddressInterface;

/**
 * Interface QuoteAddressInterface
 * @package Ekyna\Component\Commerce\Quote\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface QuoteAddressInterface extends SaleAddressInterface
{
    /**
     * Returns the quote this address is the invoice one.
     *
     * @return QuoteInterface|null
     */
    public function getInvoiceQuote();

    /**
     * Sets the quote this address is the invoice one.
     *
     * @param QuoteInterface $quote
     *
     * @return $this|QuoteAddressInterface
     */
    public function setInvoiceQuote(QuoteInterface $quote = null);

    /**
     * Returns the quote this address is the delivery one.
     *
     * @return QuoteInterface|null
     */
    public function getDeliveryQuote();

    /**
     * Sets the quote this address is the delivery one.
     *
     * @param QuoteInterface $quote
     *
     * @return $this|QuoteAddressInterface
     */
    public function setDeliveryQuote(QuoteInterface $quote = null);

    /**
     * Returns the related quote.
     *
     * @return QuoteInterface|null
     */
    public function getQuote();
}
