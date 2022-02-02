<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Quote\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractSaleAddress;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Quote\Model;

/**
 * Class QuoteAddress
 * @package Ekyna\Component\Commerce\Quote\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteAddress extends AbstractSaleAddress implements Model\QuoteAddressInterface
{
    protected ?Model\QuoteInterface $invoiceQuote  = null;
    protected ?Model\QuoteInterface $deliveryQuote = null;

    public function getInvoiceQuote(): ?Model\QuoteInterface
    {
        return $this->invoiceQuote;
    }

    public function setInvoiceQuote(?Model\QuoteInterface $quote): Model\QuoteAddressInterface
    {
        if ($quote === $this->invoiceQuote) {
            return $this;
        }

        if ($previous = $this->invoiceQuote) {
            $this->invoiceQuote = null;
            $previous->setInvoiceAddress(null);
        }

        if ($this->invoiceQuote = $quote) {
            $this->invoiceQuote->setInvoiceAddress($this);
        }

        return $this;
    }

    public function getDeliveryQuote(): ?Model\QuoteInterface
    {
        return $this->deliveryQuote;
    }

    public function setDeliveryQuote(?Model\QuoteInterface $quote): Model\QuoteAddressInterface
    {
        if ($quote === $this->deliveryQuote) {
            return $this;
        }

        if ($previous = $this->deliveryQuote) {
            $this->deliveryQuote = null;
            $previous->setDeliveryAddress(null);
        }

        if ($this->deliveryQuote = $quote) {
            $this->deliveryQuote->setDeliveryAddress($this);
        }

        return $this;
    }

    public function getQuote(): ?Model\QuoteInterface
    {
        return $this->invoiceQuote ?: $this->deliveryQuote;
    }

    public function getSale(): ?SaleInterface
    {
        return $this->getQuote();
    }
}
