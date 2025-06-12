<?php

declare(strict_types=1);

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
     */
    public function getInvoiceQuote(): ?QuoteInterface;

    /**
     * Sets the quote this address is the invoice one.
     */
    public function setInvoiceQuote(?QuoteInterface $quote): QuoteAddressInterface;

    /**
     * Returns the quote this address is the delivery one.
     */
    public function getDeliveryQuote(): ?QuoteInterface;

    /**
     * Sets the quote this address is the delivery one.
     */
    public function setDeliveryQuote(?QuoteInterface $quote): QuoteAddressInterface;

    /**
     * Returns the quote this address is the final one.
     */
    public function getDestinationQuote(): ?QuoteInterface;

    /**
     * Sets the quote this address is the final one.
     */
    public function setDestinationQuote(?QuoteInterface $quote): QuoteAddressInterface;

    /**
     * Returns the related quote.
     */
    public function getQuote(): ?QuoteInterface;
}
